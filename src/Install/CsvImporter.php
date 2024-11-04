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
    public static function import(): array|string
    {
        $provincias = self::processProvincias();

        if (is_string($provincias)) {
            return $provincias;
        }

        $codigoPostalMunicipios = self::processCodigoPostalMunicipios();

        if (is_string($codigoPostalMunicipios)) {
            return $codigoPostalMunicipios;
        }

        return self::processMunicipios($codigoPostalMunicipios);
    }

    private static function readCsv(string $fileName, string $modelClass): array|string
    {
        $result = [];

        try {
            $path = self::validateCsvFileExist($fileName);
            $handle = fopen($path, "r");
            $line = [];

            if ($handle === false) {
                throw new Exception("Fail opening CSV file $fileName");
            }

            $headers = self::getCsvHeaders($handle);

            while ($line = fgetcsv($handle, 1000, ',')) {
                $data = array_combine($headers, $line);

                $result[] = new $modelClass($data);
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    /**
     * Checks if the configured file exists throw an exception on case of error
     * @throws \Exception
     * @param string $fileName
     * @return string Path to the file
     */
    private static function validateCsvFileExist(string $fileName): string
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
    private static function getCsvHeaders(mixed $handle): array
    {
        $headers = fgetcsv($handle, 1000, ",");

        if (empty($headers)) {
            throw new Exception('The first line on the CSV is corrupt');
        }

        return $headers;
    }

    /**
     * Reads provincia from the csv file
     * @return array<int, Provincia>|string
     */
    private static function processProvincias(): array|string
    {
        /**
         * @var Provincia[] $data
         */
        $data = self::readCsv('provincias.csv', Provincia::class);
        $result = [];

        foreach ($data as $item) {
            $nombre = $item->nombre;

            $provincia = new DbProvincia([
                'nombre' => $nombre,
                'fullText' => self::cleanString($nombre)
            ]);

            $provincia->save();

            $result[$item->codigo] = $item;
        }

        ksort($result);

        return $result;
    }

    /**
     * Reads codigo postal municipio from the csv file
     * @return array<int, CodigoPostalMunicipio>||string
     */
    private static function processCodigoPostalMunicipios(): array|string
    {
        /**
         * @var CodigoPostalMunicipio[] $data
         */
        $data = self::readCsv('codigo_postal_municipio.csv', CodigoPostalMunicipio::class);

        if (is_string(value: $data)) {
            return $data;
        }

        foreach ($data as $item) {
            $result[self::getMunicipiosKey($item->codigo_municipio)] = $item;
        }

        ksort($result);

        return $result;
    }

    private static function getMunicipiosKey(mixed $codigoMunicipio): string
    {
        return "key_$codigoMunicipio";
    }

    /**
     * @param CodigoPostalMunicipio[] $codigoPostalMunicipios
     */
    private static function processMunicipios(array &$codigoPostalMunicipios): array|string
    {
        $municipios = self::readCsv('municipios.csv', Municipio::class);

        if (is_string($municipios)) {
            return $municipios;
        }

        return self::saveMunicipios($municipios, $codigoPostalMunicipios);
    }

    /**
     * @param Municipio[] $municipios
     * @param CodigoPostalMunicipio[] $codigoPostalMunicipios
     */
    private static function saveMunicipios(array &$municipios, array &$codigoPostalMunicipios): array
    {
        $count = 0;
        $errors = [];

        foreach ($municipios as $municipio) {
            try {
                $codigoMunicipio = self::getCodigoMunicipio($municipio);
                $codigoPostal = self::getCodigoPostal($codigoPostalMunicipios, $codigoMunicipio, $errors);
                $nombre = $municipio->nombre;

                $data = [
                    'codigo' => $codigoMunicipio,
                    'codigo_provincia' => $municipio->codigo_provincia,
                    'codigo_postal' => $codigoPostal,
                    'nombre' => $nombre,
                    'fullText' => self::cleanString($nombre)
                ];

                $dbModel = new DbMunicipio($data);
                $dbModel->save();

                ++$count;
            } catch (Exception $exception) {
                $errors[] = [
                    'municipio' => $municipio->toArray(),
                    'error' => $exception->getMessage()
                ];
            }
        }


        return [
            'count' => $count,
            'errors' => [
                'count' => count($errors),
                'errors' => $errors
            ]
        ];
    }

    private static function getCodigoMunicipio(Municipio $municipio): string
    {
        return $municipio->codigo_provincia . $municipio->codigo;
    }

    /**
     * @param CodigoPostalMunicipio[] $codigoPostalMunicipios
     * @param string @codigoMunicipio
     * @param array $errors
     */
    private static function getCodigoPostal(array &$codigoPostalMunicipios, string $codigoMunicipio, array &$errors): string
    {
        $codigoPostalMunicipio = $codigoPostalMunicipios[self::getMunicipiosKey($codigoMunicipio)];

        if ((empty($codigoPostalMunicipio) || empty($codigoPostalMunicipio->codigo_postal))) {
            $errors[] = [
                'municipio' => $codigoMunicipio,
                'error' => 'codigo postal not found'
            ];

            return '';
        }

        return $codigoPostalMunicipio->codigo_postal;
    }

    private static function cleanString(string $string): string
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