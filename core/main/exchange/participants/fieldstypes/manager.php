<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use
    Throwable,
    InvalidArgumentException,
    RuntimeException,
    Main\Helpers\Logger,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    Main\Helpers\Database\DB;
/** ***********************************************************************************************
 * Participant fields types manager
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class Manager
{
    public const
        ID_FIELD_TYPE                   = 'item-id';
    private static
        $availableFieldsTypes           = [],
        $availableFieldsTypesQueried    = false;
    /** **********************************************************************
     * get field by type
     *
     * @param   string  $type               field type
     * @return  Field                       field
     * @throws  InvalidArgumentException    unknown field type or field constructing error
     ************************************************************************/
    public static function getField(string $type) : Field
    {
        try
        {
            $className = self::getFieldClassName($type);
            return new $className;
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
        catch (Throwable $exception)
        {
            throw new InvalidArgumentException("unknown field type $type");
        }
    }
    /** **********************************************************************
     * get available fields types
     *
     * @return  array                       available fields types
     ************************************************************************/
    public static function getAvailableFieldsTypes() : array
    {
        if (!self::$availableFieldsTypesQueried)
        {
            self::$availableFieldsTypes         = self::queryAvailableFieldsTypes();
            self::$availableFieldsTypesQueried  = true;

            if (count(self::$availableFieldsTypes) <= 0)
            {
                Logger::getInstance()->addWarning(' Participant fields types list is empty');
            }
        }

        return self::$availableFieldsTypes;
    }
    /** **********************************************************************
     * get field class name
     *
     * @param   string  $type               field type
     * @return  string                      field class name
     ************************************************************************/
    private static function getFieldClassName(string $type) : string
    {
        $typeExplode = explode('-', $type);

        foreach ($typeExplode as $index => $part)
        {
            $typeExplode[$index] = ucfirst($part);
        }

        $className              = implode('', $typeExplode).'Field';
        $fieldClassNameExplode  = explode('\\', Field::class);

        array_pop($fieldClassNameExplode);
        $fieldClassNameExplode[] = $className;

        return implode('\\', $fieldClassNameExplode);
    }
    /** **********************************************************************
     * query available fields types
     *
     * @return  array                       available fields types
     ************************************************************************/
    private static function queryAvailableFieldsTypes() : array
    {
        $result         = [];
        $queryResult    = null;

        try
        {
            $queryResult = DB::getInstance()->query("SELECT `CODE` FROM fields_types");
        }
        catch (DBConnectionException $exception)
        {
            return $result;
        }
        catch (DBQueryException $exception)
        {
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            try
            {
                $result[] = $queryResult->pop()->get('CODE');
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
}