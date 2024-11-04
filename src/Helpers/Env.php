<?php

declare(strict_types=1);

namespace CodigosPoblacion\Helpers;

use PhpEnv\EnvManager;

trait Env
{
    private static ?array $env = null;

    public static function getEnv(): array|string
    {
        if (self::$env !== null) {
            return self::$env;
        }

        $appDir = Dir::getRootDir();
        $env = EnvManager::parse("$appDir/.env");

        if (empty($env)) {
            return 'env file does not exists';
        }

        self::$env = $env;

        return $env;
    }
}