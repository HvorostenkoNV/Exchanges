<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    PDOException,
    PDO;
/** ***********************************************************************************************
 * Class for creating/deleting application temp database records
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class TempDBRecordsCreator
{
    private static $pdo = null;
    private
        $tempDBTables   = [],
        $tempDBRecords  = [];
    /** **********************************************************************
     * get all database records
     *
     * @return  array                       all database records
     ************************************************************************/
    public static function getAllDBRecords() : array
    {
        $result = [];

        try
        {
            $queryResult = self::getPDO()->query('SHOW TABLES');
            foreach ($queryResult->fetchAll(PDO::FETCH_ASSOC) as $row)
            {
                $result[array_pop($row)] = [];
            }
        }
        catch (PDOException $exception)
        {

        }

        foreach ($result as $table => $items)
        {
            $queryResult = self::getPDO()->query("SELECT * FROM $table");
            foreach ($queryResult->fetchAll(PDO::FETCH_ASSOC) as $row)
            {
                $result[$table][] = $row;
            }
        }

        return $result;

    }
    /** **********************************************************************
     * get PDO statement
     *
     * @return  PDO                         PDO connection statement
     ************************************************************************/
    private static function getPDO() : PDO
    {
        if (!self::$pdo)
        {
            $host       = $GLOBALS['DB_HOST'];
            $name       = $GLOBALS['DB_NAME'];
            $login      = $GLOBALS['DB_LOGIN'];
            $password   = $GLOBALS['DB_PASSWORD'];

            self::$pdo = new PDO
            (
                "mysql:dbname=$name;host=$host",
                $login,
                $password
            );
        }

        return self::$pdo;
    }
    /** **********************************************************************
     * create temp database record
     *
     * @param   string  $table              database table name
     * @param   array   $item               items info, array of key => value
     * @return  int                         new record id
     ************************************************************************/
    public function createTempRecord(string $table, array $item = []) : int
    {
        $lastInsertedId = (int) self::getPDO()->lastInsertId();
        $newInsertedId  = 0;
        $sqlQuery       = $this->getQueryForDBTempRecord($table, $item);

        try
        {
            self::getPDO()->prepare($sqlQuery)->execute(array_values($item));
            $newInsertedId = (int) self::getPDO()->lastInsertId();
            if ($newInsertedId == $lastInsertedId)
            {
                $newInsertedId = 0;
            }
        }
        catch (PDOException $exception)
        {

        }

        if (!array_key_exists($table, $this->tempDBRecords))
        {
            $this->tempDBRecords[$table] = [];
        }
        if ($newInsertedId > 0)
        {
            $this->tempDBRecords[$table][] = $newInsertedId;
        }

        return $newInsertedId;
    }
    /** **********************************************************************
     * create temp database table
     *
     * @param   string  $table              database table name
     * @param   array   $columns            database columns
     * @return  bool                        creating success
     ************************************************************************/
    public function createTempTable(string $table, array $columns) : bool
    {
        $columnsParams = implode(', ', $columns);

        try
        {
            $result = self::getPDO()->query("CREATE TABLE $table ($columnsParams)");
            $this->tempDBTables[] = $table;
            return $result ? true : false;
        }
        catch (PDOException $exception)
        {
            return false;
        }
    }
    /** **********************************************************************
     * drop temp database changes
     ************************************************************************/
    public function dropTempChanges() : void
    {
        $this->dropTempRecords();
        $this->dropTempTables();
    }
    /** **********************************************************************
     * get query for creating database new temp record
     *
     * @param   string  $table              database table name
     * @param   array   $item               items info, array of key => value
     * @return  string                      query
     ************************************************************************/
    private function getQueryForDBTempRecord(string $table, array $item = []) : string
    {
        $fields = [];
        $values = [];

        foreach ($item as $key => $value)
        {
            $fields[]   = '`'.$key.'`';
            $values[]   = '?';
        }

        $fieldsImploded = implode(', ', $fields);
        $valuesImploded = implode(', ', $values);

        return "INSERT INTO $table ($fieldsImploded) VALUES ($valuesImploded)";
    }
    /** **********************************************************************
     * drop temp database records
     ************************************************************************/
    private function dropTempRecords() : void
    {
        while (count($this->tempDBRecords) > 0)
        {
            foreach ($this->tempDBRecords as $table => $items)
            {
                $idColumn       = 'ID';
                $itemsImploded  = implode(', ', $items);
                $sqlQuery       = "DELETE FROM $table WHERE $idColumn IN ($itemsImploded)";
                $result         = self::getPDO()->query($sqlQuery);

                unset($this->tempDBRecords[$table]);
                if (!$result)
                {
                    $this->tempDBRecords[$table] = $items;
                }
            }
        }
    }
    /** **********************************************************************
     * drop temp database tables
     ************************************************************************/
    private function dropTempTables() : void
    {
        foreach ($this->tempDBTables as $table)
        {
            self::getPDO()->query("DROP TABLE $table");
        }
    }
}