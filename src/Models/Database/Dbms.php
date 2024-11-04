<?php

declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

enum Dbms : string
{
    case Sqlite = 'sqlite';
    case Mysql  = 'mysql';
    case Pgsql  = 'pgsql';
}//end enum
