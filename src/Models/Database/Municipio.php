<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

/**
 * @method static bool|Municipio first(array<string, mixed> $columnValues)
 * @method static bool|Municipio[] get(array<string, mixed> $columnValues = [], int $offset = 0, int $limit = 100, array $columns = []): bool|array
 */
class Municipio extends Model
{

    /**
     * @var string $tName
     */
    public static string $tName = 'municipio';

    /**
     * @var Dbms $dbms
     */
    protected static Dbms $dbms = Dbms::Sqlite;

    /**
     * @var array<string> $columns
     */
    protected static array $columns = [
        'id',
        'codigo',
        'codigo_provincia',
        'codigo_postal',
        'nombre',
        'fullText',
    ];

    /**
     * @var ?int $id
     */
    public ?int $id;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $codigo
     */
    public string $codigo;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $codigo_provincia
     */
    public string $codigo_provincia;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $codigo_postal
     */
    public string $codigo_postal;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $nombre
     */
    public string $nombre;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    /**
     * @var string $fullText
     */
    public string $fullText;// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps


}//end class
