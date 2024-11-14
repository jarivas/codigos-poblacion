<?php

declare(strict_types=1);

namespace CodigosPoblacion\Helpers;
use CodigosPoblacion\Models\Database\Connection;

class InstallHelper
{
    use Dir, Env;

    public static function setUp(): void
    {
        $connection = Connection::getInstance();

        $connection->exec('DROP TABLE IF EXISTS municipio;');
        $sql = <<<SQL
CREATE TABLE municipio (
    id	INTEGER,
    codigo	TEXT NOT NULL,
    codigo_provincia	TEXT NOT NULL,
    codigo_postal	TEXT NOT NULL,
    nombre	TEXT NOT NULL,
    fullText	TEXT NOT NULL,
    PRIMARY KEY(id AUTOINCREMENT)
);
SQL;
        $connection->exec($sql);


        $connection->exec('DROP TABLE IF EXISTS provincia;');

        $sql = <<<SQL
CREATE TABLE provincia (
	id	INTEGER NOT NULL,
	codigo	TEXT NOT NULL,
	nombre	TEXT NOT NULL,
	fullText	TEXT NOT NULL,
	PRIMARY KEY(id)
);
SQL;
        $connection->exec($sql);
        
        $connection->exec('CREATE INDEX provincia_codigo_IDX ON provincia (codigo);');
    }

}