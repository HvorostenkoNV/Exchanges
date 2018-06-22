<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    RuntimeException,
    PDOException,
    PDO;
/** ***********************************************************************************************
 * Class for creating project temp DB recording
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class DBRecordsGenerator
{
    private
        $pdo        = null,
        $records    = [];
    /** **********************************************************************
     * constructor
     * @throws  RuntimeException            DB record writing error
     ************************************************************************/
    public function __construct()
    {
        try
        {
            $this->pdo = $this->createPDO();
        }
        catch (PDOException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * create temp database record
     *
     * @param   string  $table              database table name
     * @param   array   $item               items info
     * @return  int                         new record id
     * @throws  RuntimeException            DB record writing error
     ************************************************************************/
    public function generateRecord(string $table, array $item = []) : int
    {
        try
        {
            $lastInsertedId = (int) $this->pdo->lastInsertId();
            $sqlQuery       = $this->constructNewItemRecordingQuery($table, $item);
            $writingResult  = $this->pdo->prepare($sqlQuery)->execute(array_values($item));
            $newInsertedId  = (int) $this->pdo->lastInsertId();

            if (!$writingResult)
            {
                $error = var_export($this->pdo->errorInfo());
                throw new RuntimeException($error);
            }

            if ($newInsertedId == $lastInsertedId)
            {
                return 0;
            }

            if (!array_key_exists($table, $this->records))
            {
                $this->records[$table] = [];
            }

            $this->records[$table][] = $newInsertedId;
            return $newInsertedId;
        }
        catch (PDOException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * clean temp DB records
     ************************************************************************/
    public function clean() : void
    {
        while (count($this->records) > 0)
        {
            foreach ($this->records as $table => $items)
            {
                $idColumn       = 'ID';
                $itemsImploded  = implode(', ', $items);
                $sqlQuery       = "DELETE FROM $table WHERE $idColumn IN ($itemsImploded)";
                $result         = $this->pdo->query($sqlQuery);

                unset($this->records[$table]);
                if (!$result)
                {
                    $this->records[$table] = $items;
                }
            }
        }
    }
    /** **********************************************************************
     * get PDO connection
     *
     * @return  PDO                         PDO connection
     * @throws  PDOException                connection error
     ************************************************************************/
    private function createPDO() : PDO
    {
        $host       = $GLOBALS['DB_HOST'];
        $name       = $GLOBALS['DB_NAME'];
        $login      = $GLOBALS['DB_LOGIN'];
        $password   = $GLOBALS['DB_PASSWORD'];

        try
        {
            $pdo = new PDO
            (
                "mysql:dbname=$name;host=$host",
                $login,
                $password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        }
        catch (PDOException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get query for creating database new temp record
     *
     * @param   string  $table              database table name
     * @param   array   $item               items info, array of key => value
     * @return  string                      query
     ************************************************************************/
    private function constructNewItemRecordingQuery(string $table, array $item = []) : string
    {
        $fields             = array_keys($item);
        $fields             = array_map(function($value) {return "`$value`";}, $fields);
        $fieldsPlaceholder  = implode(', ', $fields);
        $valuesPlaceholder  = rtrim(str_repeat('?, ', count($item)), ', ');

        return "INSERT INTO $table ($fieldsPlaceholder) VALUES ($valuesPlaceholder)";
    }
}