<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
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
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to get procedures: $error");
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            $procedureName      = $queryResult->pop()->get('NAME');
            $procedureClassName = self::getProcedureFullClassName($procedureName);

            try
            {
                $result->push(new $procedureClassName);
            }
            catch (Throwable $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("Failed to create procedure \"$procedureClassName\": $error");
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
        $names      = $filter ? $filter->get('NAME')        : null;

        $names = array_filter
        (
            is_array($names) ? $names : [$names],
            function($value)
            {
                return is_string($value) && strlen($value) > 0;
            }
        );

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
            $valueTemplates = [];
            foreach ($filter->get('NAME') as $value)
            {
                $sqlQueryParams[]   = $value;
                $valueTemplates[]   = '?';
            }
            $sqlWhereClause[] = 'NAME IN ('.implode(', ', $valueTemplates).')';
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
     * get procedure class full name by procedure name
     *
     * @param   string  $name               procedure name
     * @return  string                      procedure class name
     ************************************************************************/
    private static function getProcedureFullClassName(string $name) : string
    {
        return __NAMESPACE__.'\\'.$name;
    }
}