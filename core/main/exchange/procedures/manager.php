<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    DomainException,
    Throwable,
    RuntimeException,
    InvalidArgumentException,
    Main\Data\Map,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Helpers\Data\DBQueryResult,
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
     * @param   Map|null    $filter         filter
     * @return  ProceduresSet               queue of procedures
     * @throws
     ************************************************************************/
    public static function getProcedures(Map $filter = null) : ProceduresSet
    {
        $result         = new ProceduresSet;
        $db             = null;
        $logger         = Logger::getInstance();
        $filter         = self::validateFilter($filter);
        $queryResult    = null;

        try
        {
            $queryResult = self::queryProcedures($filter);
            while (!$queryResult->isEmpty())
            {
                $procedureName  = $queryResult->pop()->get('NAME');
                $procedure      = self::createProcedure($procedureName);
                $result->push($procedure);
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to get procedures: $error");
        }
        catch (DomainException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to create procedure: $error");
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
        $names      = $filter ? $filter->get('NAME')        : null;
        $names      = is_array($names) ? $names : [$names];
        $names      = array_filter($names, function($value)
        {
            return is_string($value) && strlen($value) > 0;
        });

        try
        {
            if (is_bool($activity))
            {
                $result->set('ACTIVITY', $activity);
            }
            if (count($names) > 0)
            {
                $result->set('NAME', $names);
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
     * @throws  RuntimeException            db connection error
     ************************************************************************/
    private static function queryProcedures(Map $filter) : DBQueryResult
    {
        $sqlQuery       = 'SELECT NAME FROM procedures';
        $sqlQueryParams = [];
        $sqlWhereClause = [];

        if ($filter->hasKey('ACTIVITY'))
        {
            $sqlQueryParams[]   = $filter->get('ACTIVITY') ? 'Y' : 'N';
            $sqlWhereClause[]   = 'ACTIVITY = ?';
        }
        if ($filter->hasKey('NAME'))
        {
            $names              = $filter->get('NAME');
            $sqlQueryParams     = array_merge($sqlQueryParams, $names);
            $placeholder        = rtrim(str_repeat('?, ', count($names)), ', ');
            $sqlWhereClause[]   = "NAME IN ($placeholder)";
        }

        if (count($sqlWhereClause) > 0)
        {
            $sqlQuery .= ' WHERE '.implode(' AND ', $sqlWhereClause);
        }

        try
        {
            return DB::getInstance()->query($sqlQuery, $sqlQueryParams);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * create procedure by name
     *
     * @param   string  $name               procedure name
     * @return  Procedure                   procedure
     * @throws  DomainException             creating procedure error
     ************************************************************************/
    private static function createProcedure(string $name) : Procedure
    {
        $fullName = __NAMESPACE__.'\\'.$name;

        try
        {
            return new $fullName;
        }
        catch (Throwable $exception)
        {
            throw new DomainException("creating procedure \"$fullName\" error");
        }
    }
}