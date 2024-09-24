<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

/**
 * @method static bool|Provincia first(array<string, mixed> $columnValues)
 * @method static bool|Provincia[] get(array<string, mixed> $columnValues = [], int $offset = 0, int $limit = 100, array $columns = []): bool|array
 */
class Provincia extends Model
{

    /**
     * @var string $tName
     */
    public static string $tName = 'provincia';

    /**
     * @var Dbms $dbms
     */
    protected static Dbms $dbms = Dbms::Sqlite;

    /**
     * @var array<string> $columns
     */
    protected static array $columns = [
        'id',
        'nombre',
        'fullText',
    ];

    /**
     * @var ?int $id
     */
    public ?int $id;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $nombre
     */
    public string $nombre;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $fullText
     */
    public string $fullText;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps


}//end class
