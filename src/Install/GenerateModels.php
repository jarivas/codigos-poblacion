<?php

declare(strict_types=1);

namespace CodigosPoblacion\Install;

use CodigosPoblacion\Helpers\InstallHelper;
use SqlModels\DbConnectionInfo;
use SqlModels\GenerationSqlite;
use SqlModels\Dbms;

class GenerateModels
{

    public const TARGET_FOLDER = '/src/Models/Database';
    public const NAMESPACE = 'CodigosPoblacion\Models\Database';

    public static function generate(): bool|string
    {
        $env = InstallHelper::getEnv();
        $rootDir = InstallHelper::getRootDir();
        $dataDir = InstallHelper::getDataDir();

        $targetFolder = "$rootDir/src/Models/Database";
        $dbInfo = new DbConnectionInfo(Dbms::Sqlite, $dataDir, $env['DB_FILE']);
        $generation = new GenerationSqlite($dbInfo, $targetFolder, self::NAMESPACE);

        return $generation->process();
    }
}