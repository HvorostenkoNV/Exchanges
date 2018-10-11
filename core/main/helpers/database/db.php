<?php
declare(strict_types=1);

namespace Main\Helpers\Database;

use
    PDOException,
    RuntimeException,
    Main\Helpers\Database\Exceptions\ConnectionException,
    Main\Helpers\Database\Exceptions\QueryException,
    Main\Helpers\Database\Exceptions\InvalidArgumentException,
    PDO,
    PDOStatement,
    Main\Singleton,
    Main\Helpers\Config,
    Main\Helpers\Logger,
    Main\Helpers\Database\Data\DBRow,
    Main\Helpers\Database\Data\DBQueryResult;
/** ***********************************************************************************************
 * Application DB class
 * Provides methods for work with database
 *
 * @package exchange_helpers
 * @method  static DB getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class DB
{
    use Singleton;

    private
        $pdo                = null,
        $preparedQueries    = [],
        $lastInsertId       = 0;
    /** **********************************************************************
     * constructor
     *
     * @throws  ConnectionException             db connection error
     ************************************************************************/
    private function __construct()
    {
        $config = Config::getInstance();
        $logger = Logger::getInstance();

        try
        {
            $this->pdo = $this->getNewPDO
            (
                $config->getParam('db.host'),
                $config->getParam('db.name'),
                $config->getParam('db.login'),
                $config->getParam('db.password')
            );

            $logger->addNotice('DB object: connection success');
            $logger->addNotice('DB object: created successfully');
        }
        catch (PDOException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("DB object: creating failed, \"$error\"");
            throw new ConnectionException($error);
        }
    }
    /** **********************************************************************
     * run DB query and get result
     *
     * @param   string  $sqlQuery               sql query string
     * @param   array   $params                 query params for preparing
     * @return  DBQueryResult                   query result in rows
     * @throws  QueryException                  query error
     ************************************************************************/
    public function query(string $sqlQuery, array $params = []) : DBQueryResult
    {
        $this->lastInsertId = 0;

        try
        {
            $result             = new DBQueryResult;
            $preparedQuery      = $this->getPreparedQueryStatement($sqlQuery);
            $pdoLastInsertId    = (int) $this->pdo->lastInsertId();
            $queryResult        = $this->executeQueryStatement($preparedQuery, $params);
            $newInsertedId      = (int) $this->pdo->lastInsertId();

            if ($newInsertedId != $pdoLastInsertId)
            {
                $this->lastInsertId = $newInsertedId;
            }

            foreach ($queryResult as $row)
            {
                $fieldValues = new DBRow;
                foreach ($row as $key => $value)
                {
                    $fieldValues->set($key, $value);
                }
                $result->push($fieldValues);
            }

            return $result;
        }
        catch (PDOException $exception)
        {
            $error = $exception->getMessage();
            throw new QueryException("query error \"$error\" on preparing query \"$sqlQuery\"");
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            throw new QueryException("query error \"$error\" on executing query \"$sqlQuery\"");
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            throw new QueryException("error on constructing query result, \"$error\"");
        }
    }
    /** **********************************************************************
     * get last inserted item id
     *
     * @return  int                             last inserted item id
     ************************************************************************/
    public function getLastInsertId() : int
    {
        return $this->lastInsertId;
    }
    /** **********************************************************************
     * get new PDO
     *
     * @param   string  $dbHost                 database host
     * @param   string  $dbName                 database name
     * @param   string  $dbLogin                database login
     * @param   string  $dbPassword             database password
     * @return  PDO                             new PDO object
     * @throws  PDOException                    new PDO object creating failed
     ************************************************************************/
    private function getNewPDO(string $dbHost, string $dbName, string $dbLogin, string $dbPassword) : PDO
    {
        try
        {
            $pdo = new PDO
            (
                "mysql:dbname=$dbName;host=$dbHost",
                $dbLogin,
                $dbPassword,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
                ]
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
     * get prepared query statement
     *
     * @param   string  $sqlQuery               sql query
     * @return  PDOStatement                    prepared query statement
     * @throws  PDOException                    preparing error
     ************************************************************************/
    private function getPreparedQueryStatement(string $sqlQuery) : PDOStatement
    {
        try
        {
            if (!array_key_exists($sqlQuery, $this->preparedQueries))
            {
                $this->preparedQueries[$sqlQuery] = $this->pdo->prepare($sqlQuery);
            }

            return $this->preparedQueries[$sqlQuery];
        }
        catch (PDOException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * execute prepared query statement
     *
     * @param   PDOStatement    $preparedQuery  prepared query statement
     * @param   array           $params         query params for preparing
     * @return  array                           query result in rows
     * @throws  RuntimeException                executing error
     ************************************************************************/
    private function executeQueryStatement(PDOStatement $preparedQuery, array $params) : array
    {
        try
        {
            if (!$preparedQuery->execute($params))
            {
                throw new RuntimeException($preparedQuery->errorInfo()[2]);
            }
        }
        catch (PDOException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }

        try
        {
            return $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $exception)
        {
            return [];
        }
    }
}