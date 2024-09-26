<?php

declare(strict_types=1);

namespace CodigosPoblacion\Install;

use CodigosPoblacion\Helpers\InstallHelper;
use CodigosPoblacion\Models\Provincia;
use CodigosPoblacion\Models\Municipio;
use CodigosPoblacion\Models\Database\Provincia as DbProvincia;
use CodigosPoblacion\Models\Database\Municipio as DbMunicipio;
use Exception;

class CsvImporter
{
    public static function import(): string|int
    {
        $count = 0;
        $provincias = self::processProvincias();

        if (is_string($provincias)) {
            return $provincias;
        }

        $municipios = self::readCsv('municipios.csv', Municipio::class);

        if (is_string($municipios)) {
            return $municipios;
        }

        try {
            foreach ($municipios as $municipio) {
                $codigo = $municipio->codigo;
                $nombre = $municipio->nombre;
                $provincia = $provincias[$codigo];

                $data = [
                    'codigo' => $codigo . $municipio->codigo_municipio,
                    'codigo_control' => $municipio->codigo_control,
                    'provincia' => $provincia->id,
                    'nombre' => $nombre,
                    'fullText' => self::cleanString($nombre)
                ];

                $dbModel = new DbMunicipio($data);
                $dbModel->save();
                ++$count;
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $count;
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
     * @return array<int, DbProvincia>|string
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

            $result[$item->codigo] = $provincia;
        }

        ksort($result);

        return $result;
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