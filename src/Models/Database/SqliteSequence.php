<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

/**
 * @method static bool|SqliteSequence first(array<string, mixed> $columnValues)
 * @method static bool|SqliteSequence[] get(array<string, mixed> $columnValues = [], int $offset = 0, int $limit = 100, array $columns = []): bool|array
 */
class SqliteSequence extends Model
{

    /**
     * @var string $tName
     */
    public static string $tName = 'sqlite_sequence';

    /**
     * @var Dbms $dbms
     */
    protected static Dbms $dbms = Dbms::Sqlite;

    /**
     * @var array<string> $columns
     */
    protected static array $columns = [
        'name',
        'seq',
    ];

    /**
     * @var ?mixed $name
     */
    public ?mixed $name;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var ?mixed $seq
     */
    public ?mixed $seq;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps


}//end class
