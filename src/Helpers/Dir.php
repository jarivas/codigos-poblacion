<?php

declare(strict_types=1);

namespace CodigosPoblacion\Helpers;

trait Dir
{
    private static ?string $rootDir = null;
    private static ?string $dataDir = null;
    
    public static function getRootDir(): string
    {
        if (self::$rootDir === null) {
            self::$rootDir = dirname(__DIR__, 2);
        }
        
        return self::$rootDir;
    }

    public static function getDataDir(): string
    {
        if (self::$dataDir !== null) {
            return self::$dataDir;
        }

        $dataDir = self::getRootDir().'/data/';

        self::$dataDir = $dataDir;

        return $dataDir;
    }
}