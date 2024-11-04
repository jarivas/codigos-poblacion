<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;


class SqlGenerator
{


    /**
     * @param Dbms $dbms
     * @param string $tName
     * @param array<string, string> $params
     * @param array<string> $selectedColumns
     * @param null|array<int, array<string, string>> $joins
     * @param null|array<int, array<string, string>> $where
     * @param null|string $groupBy
     * @param null|array<int, array<string, string>> $having
     * @param null|array<int, array<string, string>> $orderBy
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public static function generateSelect(
        Dbms $dbms,
        string $tName,
        array &$params,
        array $selectedColumns,
        null|array $joins=null,
        null|array $where=null,
        null|string $groupBy=null,
        null|array $having=null,
        null|array $orderBy=null,
        int $limit=1000,
        int $offset=0
    ): string {
        $c = self::generateColumns($dbms, $selectedColumns);

        $j = self::generateJoins($dbms, $joins);

        $w = self::generateConditions($dbms, 'WHERE', $where, $params);

        $g = self::generateGroupBy($dbms, $groupBy);

        $h = self::generateConditions($dbms, 'HAVING', $having, $params);

        $o = self::generateOrderBy($dbms, $orderBy);

        $l = self::generateLimit($limit);

        $of = self::generateOffsset($offset);

        $tName = ($dbms != Dbms::Pgsql) ? $tName : '"'.$tName.'"';

        return "SELECT $c FROM $tName $j$w$g$h$o$l$of";

    }//end generateSelect()


    /**
     * @param Dbms $dbms
     * @param string $tName
     * @param array<string, string> $params
     * @return string
     */
    public static function generateInsert(
        Dbms $dbms,
        string $tName,
        array $params
        ): string
    {
        $cols    = array_keys($params);
        $columns = self::generateColumns($dbms, $cols);
        $values  = self::generateValues($cols);

        $tName = ($dbms != Dbms::Pgsql) ? $tName : '"'.$tName.'"';

        return "INSERT INTO $tName ($columns) VALUES (:$values);";

    }//end generateInsert()


    /**
     * @param Dbms $dbms
     * @param string $tName
     * @param array<string, string> $params
     * @param array<int, array<string, string>> $where
     * @param null|array<int, array<string, string>> $orderBy
     * @param int $limit
     * @return string
     */
    public static function generateUpdate(
        Dbms $dbms,
        string $tName,
        array &$params,
        array $where=[],
        null|array $orderBy=null,
        int $limit=0
        )
    {
        $s = self::generateSet($dbms, array_keys($params));

        $w = self::generateConditions($dbms, 'WHERE', $where, $params);

        $o = self::generateOrderBy($dbms, $orderBy);

        $l = ($limit > 0) ? self::generateLimit($limit): '';

        $tName = ($dbms != Dbms::Pgsql) ? $tName : '"'.$tName.'"';

        return "UPDATE $tName SET $s$w$o$l";

    }//end generateUpdate()


    /**
     * @param Dbms $dbms
     * @param string $tName
     * @param array<string, string> $params
     * @param array<int, array<string, string>> $where
     * @param null|array<int, array<string, string>> $orderBy
     * @param int $limit
     * @return string
     */
    public static function generateDelete(
        Dbms $dbms,
        string $tName,
        array &$params,
        array $where=[],
        null|array $orderBy=null,
        int $limit=0
        ): string
    {
        $w = self::generateConditions($dbms, 'WHERE', $where, $params);

        $o = self::generateOrderBy($dbms, $orderBy);

        $l = ($limit > 0) ? self::generateLimit($limit): '';

        $tName = ($dbms != Dbms::Pgsql) ? $tName : '"'.$tName.'"';

        return "DELETE FROM $tName $w";

    }//end generateDelete()


    /**
     * @param Dbms $dbms
     * @param array<int, string> $cols
     * @return string
     */
    protected static function generateColumns(Dbms $dbms, array $cols): string
    {
        return ($dbms != Dbms::Pgsql) ? implode(', ', $cols) : '"'.implode('", "', $cols).'"';

    }//end generateColumns()


    /**
     * @param Dbms $dbms
     * @param null|array<int, array<string, string>> $joins
     * @return string
     */
    protected static function generateJoins(Dbms $dbms, null|array $joins): string
    {
        if (empty($joins)) {
            return '';
        }

        $sql = '';
        $isPg = ($dbms == Dbms::Pgsql);

        foreach ($joins as $j) {
            $format = $isPg ? ' %s JOIN %s ON "%s" = "%s"' : ' %s JOIN %s ON %s = "%s"';
            $sql .= sprintf($format, $j['type'], $j['tableName'], $j['onCol1'], $j['onCol2']);
        }

        return $sql;

    }//end generateJoins()

    /**
     * @param Dbms $dbms
     * @param string $conditionType
     * @param array<int, array<string, string>> $conditions
     * @param array<string, string> $params
     * @return string
     */
    protected static function generateConditions(
        Dbms $dbms,
        string $conditionType,
        array $conditions,
        array &$params): string
    {
        if (empty($conditions)) {
            return '';
        }

        $sql  = " $conditionType 1=1";

        foreach ($conditions as $i => $c) {
            $operator = $c['operator'];
            $col      = ($dbms != Dbms::Pgsql) ? $c['column'] : '"'.$c['column'].'"';

            if ($operator != 'IN') {
                $sql .= sprintf(' %s %s %s :%s%s', $c['condition_operator'], $col, $operator, $c['column'], $i);
                $params[$c['column'].$i] = $c['value'];
            } else {
                $sql .= sprintf(' %s %s IN %s', $c['condition_operator'], $col, $c['value']);
            }
        }

        return $sql;

    }//end generateConditions()

    /**
     * @param Dbms $dbms
     * @param null|string $groupBy
     * @return string
     */
    protected static function generateGroupBy(Dbms $dbms, null|string $groupBy): string
    {
        if (empty($groupBy)) {
            return '';
        }

        return ($dbms != Dbms::Pgsql) ? " GROUP BY $groupBy" : " GROUP BY \"$groupBy\"";

    }//end generateGroupBy()


    /**
     * @param int $limit
     * @return string
     */
    protected static function generateLimit(int $limit=100): string
    {
        return " LIMIT $limit";

    }//end generateLimit()


    /**
     * @param int $offset
     * @return string
     */
    protected static function generateOffsset(int $offset=0): string
    {
        return " OFFSET $offset";

    }//end generateOffsset()


    /**
     * @param Dbms $dbms
     * @param null|array<array<string, string>> $orderBy
     * @return string
     */
    protected static function generateOrderBy(Dbms $dbms, null|array $orderBy): string
    {
        if (empty($orderBy)) {
            return '';
        }

        $sql = ' ORDER BY ';

        foreach ($orderBy as $order) {
            $sql .= implode(' ', $order);
        }

        return ' ORDER BY '.self::generateColumns($dbms, $orderBy);

    }//end generateOrderBy()


    /**
     * @param array<int, string> $cols
     * @return string
     */
    protected static function generateValues(array $cols): string
    {
        return implode(', :', $cols);

    }//end generateValues()


    /**
     * @param Dbms $dbms
     * @param array<int, string> $cols
     * @return string
     */
    protected static function generateSet(Dbms $dbms, array $cols): string
    {
        $sql = '';
        $isPg = ($dbms == Dbms::Pgsql);
        
        foreach ($cols as $c) {
            $sql .= $isPg ? " \"$c\" = :$c," : " $c = :$c,";
        }

        return substr($sql, 0, -1);

    }//end generateValues()


}//end class
