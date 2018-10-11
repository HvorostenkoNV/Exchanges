<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    RuntimeException,
    InvalidArgumentException,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    Main\Exchange\Procedures\Exceptions\UnknownProcedureException,
    Main\Data\Map,
    Main\Data\MapData,
    Main\Helpers\Logger,
    Main\Helpers\Database\DB,
    Main\Helpers\Database\Data\DBQueryResult,
    Main\Exchange\Procedures\Data\ProceduresSet;
/** ***********************************************************************************************
 * Application procedures manager
 * Provides procedures ability work with
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class Manager
{
    /** **********************************************************************
     * get procedures by filter
     *
     * @param   Map|null $filter            filter
     * @return  ProceduresSet               queue of procedures
     ************************************************************************/
    public static function getProcedures(Map $filter = null) : ProceduresSet
    {
        $result         = new ProceduresSet;
        $logger         = Logger::getInstance();
        $filter         = self::validateFilter($filter);
        $queryResult    = null;

        try
        {
            $queryResult = self::queryProcedures($filter);
        }
        catch (DBQueryException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Procedures manager: procedures query failed, \"$error\"");
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            try
            {
                $procedureCode  = $queryResult->pop()->get('CODE');
                $procedure      = self::createProcedure($procedureCode);
                $result->push($procedure);
            }
            catch (RuntimeException $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("Procedures manager: unexpected error on constructing procedures queue, \"$error\"");
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("Procedures manager: unexpected error on constructing procedures queue, \"$error\"");
            }
            catch (UnknownProcedureException $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("Procedures manager: procedure creating failed, \"$error\"");
            }
        }

        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * validate procedures filter
     *
     * @param   Map|null    $filter         filter
     * @return  Map                         validated filter
     ************************************************************************/
    private static function validateFilter(Map $filter = null) : Map
    {
        $result     = new MapData;
        $activity   = $filter ? $filter->get('ACTIVITY')    : null;
        $codes      = $filter ? $filter->get('CODE')        : null;
        $codes      = array_filter((array) $codes, function($value)
        {
            return is_string($value) && strlen($value) > 0;
        });

        try
        {
            if (is_bool($activity))
            {
                $result->set('ACTIVITY', $activity);
            }
            if (count($codes) > 0)
            {
                $result->set('CODE', $codes);
            }
        }
        catch (InvalidArgumentException $exception)
        {

        }

        return $result;
    }
    /** **********************************************************************
     * query procedures from database
     *
     * @param   Map $filter                 filter
     * @return  DBQueryResult               query result
     * @throws  DBQueryException            db query error
     ************************************************************************/
    private static function queryProcedures(Map $filter) : DBQueryResult
    {
        $sqlQuery       = 'SELECT CODE FROM procedures';
        $sqlQueryParams = [];
        $sqlWhereClause = [];

        if ($filter->hasKey('ACTIVITY'))
        {
            $sqlQueryParams[]   = $filter->get('ACTIVITY') ? 'Y' : 'N';
            $sqlWhereClause[]   = 'ACTIVITY = ?';
        }
        if ($filter->hasKey('CODE'))
        {
            $codes              = $filter->get('CODE');
            $sqlQueryParams     = array_merge($sqlQueryParams, $codes);
            $placeholder        = rtrim(str_repeat('?, ', count($codes)), ', ');
            $sqlWhereClause[]   = "CODE IN ($placeholder)";
        }

        if (count($sqlWhereClause) > 0)
        {
            $sqlQuery .= ' WHERE '.implode(' AND ', $sqlWhereClause);
        }

        try
        {
            return DB::getInstance()->query($sqlQuery, $sqlQueryParams);
        }
        catch (DBConnectionException $exception)
        {
            throw new DBQueryException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * create procedure by code
     *
     * @param   string  $code               procedure code
     * @return  Procedure                   procedure
     * @throws  UnknownProcedureException   creating procedure error
     ************************************************************************/
    private static function createProcedure(string $code) : Procedure
    {
        $className = __NAMESPACE__.'\\'.$code;

        try
        {
            return new $className;
        }
        catch (Throwable $exception)
        {
            throw new UnknownProcedureException($exception->getMessage());
        }
    }
}