<?php

declare(strict_types=1);

namespace CodigosPoblacion\Install;

use CodigosPoblacion\Helpers\InstallHelper;
use CodigosPoblacion\Models\CodigoPostalMunicipio;
use CodigosPoblacion\Models\Provincia;
use CodigosPoblacion\Models\Municipio;
use CodigosPoblacion\Models\Database\Provincia as DbProvincia;
use CodigosPoblacion\Models\Database\Municipio as DbMunicipio;
use Exception;

class CsvImporter
{
    /**
     * @var Provincia[] $provincias
     */
    private array $provincias = [];

    /**
     * @var Municipio[] $municipios
     */
    private array $municipios = [];

    /**
     * @var array<string, array<int, CodigoPostalMunicipio>> $codigoPostalMunicipios
     */
    private array $codigoPostalMunicipios = [];

    private array $errors = [];

    public function import(): array|string
    {
        $provincias = $this->processProvincias();

        if (is_string($provincias)) {
            return $provincias;
        }

        $codigoPostalMunicipios = $this->processCodigoPostalMunicipios();

        if (is_string($codigoPostalMunicipios)) {
            return $codigoPostalMunicipios;
        }

        return $this->processMunicipios();
    }

    private function readCsv(string $fileName, string $modelClass): array|string
    {
        $provincias = [];

        try {
            $path = $this->validateCsvFileExist($fileName);
            $handle = fopen($path, "r");
            $line = [];

            if ($handle === false) {
                throw new Exception("Fail opening CSV file $fileName");
            }

            $headers = $this->getCsvHeaders($handle);

            while ($line = fgetcsv($handle, 1000, ',')) {
                $data = array_combine($headers, $line);

                $provincias[] = new $modelClass($data);
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $provincias;
    }

    /**
     * Checks if the configured file exists throw an exception on case of error
     * @throws \Exception
     * @param string $fileName
     * @return string Path to the file
     */
    private function validateCsvFileExist(string $fileName): string
    {
        $env = InstallHelper::getEnv();

        if (is_string($env)) {
            throw new Exception($env);
        }

        $path = InstallHelper::getDataDir() . $fileName;

        if (!file_exists($path)) {
            throw new Exception("csv file does not exists: $path");
        }

        return $path;
    }

    /**
     * Read the first line to get cols or headers
     * @param mixed $handle
     * @throws \Exception
     * @return array
     */
    private function getCsvHeaders(mixed $handle): array
    {
        $headers = fgetcsv($handle, 1000, ",");

        if (empty($headers)) {
            throw new Exception('The first line on the CSV is corrupt');
        }

        return $headers;
    }

    /**
     * Reads provincia from the csv file
     * @return ?string
     */
    private function processProvincias(): ?string
    {
        /**
         * @var Provincia[] $data
         */
        $data = $this->readCsv('provincias.csv', Provincia::class);
        $provincias = [];
        $result = null;

        if (is_string($data)) {
            return $data;
        }

        foreach ($data as $item) {
            $codigo = $item->codigo;
            $nombre = $item->nombre;

            $provincia = new DbProvincia([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'fullText' => $this->cleanString($nombre)
            ]);

            $provincia->save();

            $provincias[$codigo] = $item;
        }

        ksort($provincias);

        $this->provincias = $provincias;

        return $result;
    }

    /**
     * Reads codigo postal municipio from the csv file
     * @return ?string
     */
    private function processCodigoPostalMunicipios(): ?string
    {
        /**
         * @var CodigoPostalMunicipio[] $data
         */
        $data = $this->readCsv('codigo_postal_municipio.csv', CodigoPostalMunicipio::class);
        $codigoPostalMunicipios = [];
        $result = null;

        if (is_string(value: $data)) {
            return $data;
        }

        foreach ($data as $item) {
            if (empty($codigoPostalMunicipios[$item->codigo_municipio])) {
                $codigoPostalMunicipios[$item->codigo_municipio] = [];
            }

            $codigoPostalMunicipios[$item->codigo_municipio][] = $item;
        }

        ksort($codigoPostalMunicipios);

        $this->codigoPostalMunicipios = $codigoPostalMunicipios;

        return $result;
    }

    private function processMunicipios(): array|string
    {
        $municipios = $this->readCsv('municipios.csv', Municipio::class);

        if (is_string($municipios)) {
            return $municipios;
        }

        $this->saveMunicipios($municipios);

        return $this->errors;
    }

    /**
     * @param Municipio[] $municipios
     */
    private function saveMunicipios(array &$municipios): void
    {
        $count = 0;

        foreach ($municipios as $municipio) {
            try {
                $codigoMunicipio = $municipio->getCodigoMunicipio();
                $codigoPostales = $this->getCodigoPostales( $codigoMunicipio);
                
                foreach($codigoPostales as $codigoPostal) {
                    $this->saveMunicipio($municipio, $codigoMunicipio, $codigoPostal);

                    ++$count;
                }
            } catch (Exception $exception) {
                $this->errors[] = [
                    'municipio' => $municipio->toArray(),
                    'error' => $exception->getMessage()
                ];
            }
        }

        $this->errors = [
            'count' => $count,
            'errors' => [
                'count' => count($this->errors),
                'errors' => $this->errors
            ]
        ];
    }

    private function saveMunicipio(Municipio $municipio, string $codigoMunicipio, string $codigoPostal)
    {
        $nombre = $municipio->nombre;

        $data = [
            'codigo' => $codigoMunicipio,
            'codigo_provincia' => $municipio->codigo_provincia,
            'codigo_postal' => $codigoPostal,
            'nombre' => $nombre,
            'fullText' => $this->cleanString($nombre)
        ];

        $dbModel = new DbMunicipio($data);
        $dbModel->save();

    }

    /**
     * @param string $codigoMunicipio
     * @return string[]
     */
    private function getCodigoPostales(string $codigoMunicipio): array
    {
        $codigosPostalesMunicipio = $this->codigoPostalMunicipios[$codigoMunicipio];
        $result = [];

        if (empty($codigosPostalesMunicipio)) {
            $this->errors[] = [
                'municipio' => $codigoMunicipio,
                'error' => 'codigo postal not found'
            ];

            return $result;
        }

        foreach ($codigosPostalesMunicipio as $codigoPostalMunicipio) {
            if (empty($codigoPostalMunicipio->codigo_postal)) {
                $this->errors[] = [
                    'municipio' => $codigoMunicipio,
                    'error' => 'codigo postal not found'
                ];

                return $result;
            }
        }

        return array_column($codigosPostalesMunicipio, 'codigo_postal');
    }

    private function cleanString(string $string): string
    {
        $string = trim($string);
        $string = str_replace('/', ' ', $string);
        $string = str_replace(',', '', $string);
        $string = str_replace('-', ' ', $string);
        $string = str_replace("'", ' ', $string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = iconv('ASCII//TRANSLIT', 'UTF-8', $string);
        $string = strtolower($string);

        return $string;
    }
}