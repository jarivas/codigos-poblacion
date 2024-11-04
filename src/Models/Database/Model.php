<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

use Exception;
use JsonSerializable;

class Model implements JsonSerializable
{
    
    /**
     * @var string $tName
     */
    public static string $tName = '';

    /**
     * @var Dbms $dbms
     */
    protected static Dbms $dbms;

    /**
     * @var array<string> $columns
     */
    protected static array $columns = [];

    /**
     * @var array<string> $selectedColumns
     */
    protected array $selectedColumns = [];

    /**
     * @var array<int, array<string, string>> $joins
     */
    protected array $joins = [];

    /**
     * @var array<int, array<string, string>>  $where
     */
    protected array $where = [];

    /**
     * @var null|string $groupBy
     */
    protected null|string $groupBy = null;

    /**
     * @var array<int, array<string, string>>  $having
     */
    protected $having = [];

    /**
     * @var array<int, array<string, string>>  $order
     */
    protected array $order = [];

    /**
     * @var int $limit
     */
    protected int $limit = 1;

    /**
     * @var int $limit
     */
    protected int $offset = 0;

    /**
     * @param array<string, mixed> $columnValues
     * @return bool|static
     */
    public static function first(array $columnValues = []): bool|static
    {
        // @phpstan-ignore-next-line
        $new = new static();

        $new->setValues($columnValues, true);

        $rows = $new->query(false);

        if (! is_array($rows) || empty($rows)) {
            return false;
        }

        return $rows[0];

    }//end first()

    /**
     * @param array<string, mixed> $columnValues
     * @param int $offset
     * @param int $limit
     * @param array<string> $columns
     * @return bool|array<static>
     */
    public static function get(array $columnValues = [], int $offset = 0, int $limit = 100, array $columns = []): bool|array
    {
        // @phpstan-ignore-next-line
        $new = new static();

        $new->select($columns);

        $new->setValues($columnValues, true);

        $new->offset($offset);
        $new->limit($limit);

        $rows = $new->query();

        if (! is_array($rows) || empty($rows)) {
            return false;
        }

        return $rows;

    }//end get()

    /**
     * @param null|array<string, mixed> $columnValues
     */
    public function __construct(null|array $columnValues = null)
    {
        if (!empty($columnValues)) {
            $this->setValues($columnValues);
        }
    }//end __construct()
    
    /**
     * @param array<string, mixed> $columnValues
     * @param bool $where
     */
    public function setValues(array $columnValues, bool $where = false): void
    {
        foreach ($columnValues as $column => $value) {
            if (in_array($column, static::$columns)) {
                $this->$column = $value;

                if ($where) {
                    $this->where[] = [
                        'condition_operator' => 'AND',
                        'column'             => $column,
                        'operator'           => '=',
                    ];
                }
            }            
        }

    }//end setValues()

    /**
     * @param array<string> $columns
     */
    public function select(array $columns): void
    {
        $this->selectedColumns = [];

        if (empty($columns)) {
            return;
        }

        foreach($columns as $column) {
            if (in_array($column, static::$columns)) {
                $this->selectedColumns[] = $column;
            }
        }

    }//end select()

    /**
     * @param Join $joinType
     * @param string $tName
     * @param string $onCol1
     * @param string $onCol2
     */
    public function join(Join $joinType, string $tName, string $onCol1, string $onCol2): void
    {
        $this->joins[] = [
            'type'      => $joinType->value,
            'tableName' => $tName,
            'onCol1'    => $onCol1,
            'onCol2'    => $onCol2,
        ];

    }//end join()

    /**
     * @param string $column
     * @param string $operator
     * @param string $operator
     */
    public function where(string $column, string $operator, mixed $value = null): void
    {
        $this->where[] = $this->whereHelper('AND', $column, $operator, $value);

    }//end where()

    /**
     * @param string $column
     * @param string $operator
     */
    public function whereOr(string $column, string $operator, mixed $value = null): void
    {
        $this->where[] = $this->whereHelper('OR', $column, $operator, $value);

    }//end whereOr()

    /**
     * @param string $column
     * @param string $operator
     */
    public function having(string $column, string $operator, mixed $value = null): void
    {
        $this->having[] = $this->whereHelper('AND', $column, $operator, $value);

    }//end having()

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     */
    public function havingOr(string $column, string $operator, mixed $value = null): void
    {
        $this->having[] = $this->whereHelper('OR', $column, $operator, $value);

    }//end havingOr()

    /**
     * @param string  $column
     */
    public function groupBy(string $column): void
    {
        $this->groupBy = $column;

    }//end groupBy()

    /**
     * @param array<string, string> $order
     */
    public function order(array $order): void
    {
        $this->order[] = $order;

    }//end order()

    /**
     * @param int $limit
     */
    public function limit(int $limit): void
    {
        $this->limit = $limit;

    }//end limit()

    /**
     * @param int $offset
     */
    public function offset(int $offset): void
    {
        $this->offset = $offset;

    }//end offset()

    /**
     * @param string|null $pk on tables with primary no autoincrement, please use null
     * 
     * @return bool
     */
    public function delete(string|null $pk = 'id'): bool
    {
        if (empty($this->$pk)) {
            return false;
        }

        $params = [];

        $this->where($pk, '=');

        $sql = SqlGenerator::generateDelete(static::$dbms, static::$tName, $params, $this->criteriaHelper($this->where));

        unset($this->$pk);

        $this->resetFilters();

        Connection::getInstance()->executeSql($sql, $params);

        return true;

    }//end delete()

    /**
     * @param bool $resetFilter
     * @param bool $debug
     * 
     * @return bool|array<static> array of current model
     */
    public function query(bool $resetFilter = true, bool $debug = false): mixed
    {
        $cols   = static::$columns;
        $params = [];

        if (! empty($this->selectedColumns)) {
            $cols = $this->selectedColumns;

            $this->selectedColumns = [];
        }

        $sql = SqlGenerator::generateSelect(
            static::$dbms,
            static::$tName,
            $params,
            $cols,
            $this->joins,
            $this->criteriaHelper($this->where),
            $this->groupBy,
            $this->criteriaHelper($this->having),
            $this->order,
            $this->limit,
            $this->offset
        );

        if ($resetFilter) {
            $this->resetFilters();
        }

        if ($debug) {
            error_log($sql);
            error_log(print_r($params, true));
        }

        return Connection::getInstance()->get($sql, $params, static::class);

    }//end get()

    /**
     * @param string|null $pk on tables with primary no autoincrement, please use null
     */
    public function save(string|null $pk = 'id'): void
    {
        isset($this->$pk) ? $this->update($pk) : $this->insert($pk);

    }//end save()

    /**
     * @param string|null $pk on tables with primary no autoincrement, please use null
     */
    public function hydrate(string|null $pk = 'id'): void
    {
        $this->limit = 1;
        $this->offset = 0;
        if ($pk) {
            $this->where($pk,'=');
        }

        $rows = $this->query(false);

        if (! is_bool($rows)) {
            $this->setValues($rows[0]->toArray());
        }       
    }//end hydrate()

    public function __toString(): string
    {
        $data = $this->columnsToParams();

        return strval(json_encode($data, JSON_PRETTY_PRINT));

    }//end __toString()

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return $this->columnsToParams();

    }//end jsonSerialize()

    /**
     * @return array<string, mixed>
     */
    public function toArray(): mixed
    {
        return $this->columnsToParams();

    }//end toArray()

    /**
     * @param string $conditionOperator
     * @param string $column
     * @param string $operator
     * @param mixed $value
     */
    protected function whereHelper(string $conditionOperator, string $column, string $operator, mixed $value): array
    {
        return [
            'condition_operator' => $conditionOperator,
            'column' => $column,
            'operator' => $operator,
            'value'=> $value
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function columnsToParams(string|null $pk=null): array
    {
        $params = [];

        foreach (static::$columns as $column) {
            if (isset($this->$column)) {
                $params[$column] = $this->$column;
            }
        }

        if ($pk && isset($this->$pk)) {
            unset($params[$pk]);
        }

        return $params;

    }//end columnsToParams()

    /**
     * @param string|null $pk on tables with primary no autoincrement, please use null
     */
    protected function insert(string|null $pk = 'id'): void
    {
        $params     = $this->columnsToParams($pk);
        $connection = Connection::getInstance();

        $sql = SqlGenerator::generateInsert(static::$dbms, static::$tName, $params);

        $connection->executeSql($sql, $params);

        if (is_null($pk)) {
            return;
        }

        $id = $connection->lastInsertId();

        if (!$id) {
            throw new Exception('empty lastInsertId');
        }

        $this->$pk = intval($id);

    }//end insert()

    /**
     * @param string|null $pk on tables with primary no autoincrement, please use null
     */
    protected function update(string $pk = 'id'): void
    {
        $params = $this->columnsToParams($pk);

        $this->where($pk, '=');

        $sql = SqlGenerator::generateUpdate(static::$dbms, static::$tName, $params, $this->criteriaHelper($this->where));

        $this->resetFilters();

        Connection::getInstance()->executeSql($sql, $params);

    }//end update()

    /**
     * @param array $criteria array<string, mixed>
     * @return array
     */
    protected function criteriaHelper(array $criteria): array
    {
        $result = [];

        if(empty($criteria)) {
            return $result;
        }

        foreach($criteria as $c) {
            $field = $c['column'];
            
            if (empty($c['value'])) {
                $c['value'] = $this->$field;
            }            
            
            $result[] = $c;
        }

        return $result;

    }//end criteriaHelper()

    protected function resetFilters(): void
    {
        $this->joins  = [];
        $this->where  = [];
        $this->groupBy  = '';
        $this->having = [];
        $this->order  = [];
        $this->limit  = 10;
        $this->offset = 0;

    }//end resetFilters()


}//end class