<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    RuntimeException,
    SplQueue,
    Main\Helpers\DB,
    Main\Helpers\Logger;
/** ***********************************************************************************************
 * Procedures manager. Provides procedures ability work with.
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class Manager
{
    /** **********************************************************************
     * get procedures array
     * @return  SplQueue    procedures array
     * TODO
     ************************************************************************/
    public static function getProceduresList() : SplQueue
    {
        $result = new SplQueue();
        /*
        $DB     = self::getDB();
        $result = [];

        if ($DB)
            foreach ($DB->query('SELECT NAME FROM procedures WHERE ACTIVITY = ?', [1]) as $itemInfo)
            {
                $procedureNameClass = self::getProcedureClassName($itemInfo['NAME']);
                try
                {
                    $result[] = new $procedureNameClass;
                }
                catch (Throwable $error)
                {
                    Logger::getInstance()->addWarning('Failed to create procedure object "'.$itemInfo['NAME'].'": '.$error->getMessage());
                }
            }

        return $result;
        */
        return $result;
    }
    /** **********************************************************************
     * get procedure by name
     * @return  Procedure|NULL  procedure or NULL if not found
     * TODO
     ************************************************************************/
    public static function getProcedure(string $name) : ?Procedure
    {
        /*
        $DB = self::getDB();

        if ($DB && strlen($name) > 0)
            foreach ($DB->query('SELECT NAME FROM procedures WHERE ACTIVITY = ? AND NAME = ?', [1, $name]) as $itemInfo)
            {
                $procedureNameClass = self::getProcedureClassName($itemInfo['NAME']);
                try
                {
                    return new $procedureNameClass;
                }
                catch (Throwable $error)
                {
                    Logger::getInstance()->addWarning('Failed to create procedure object "'.$itemInfo['NAME'].'": '.$error->getMessage());
                }
            }

        return NULL;
        */
        return NULL;
    }
/*
    private static function getDB() : ?DB
    {
        if (self::$DB || self::$dbUnavailable)
            return self::$DB;

        try
        {
            self::$DB = DB::getInstance();
        }
        catch (RuntimeException $exception)
        {
            Logger::getInstance()->addWarning('DB error: "'.$exception->getMessage().'"');
            self::$dbUnavailable = true;
        }

        return self::$DB;
    }

    private static function getProcedureClassName(string $procedureName) : string
    {
        $result = str_replace('_', ' ', $procedureName);
        $result = ucwords($result);
        $result = implode('', explode(' ', $result));
        return '\\Main\\Exchange\\Procedures\\'.$result;
    }
*/
}