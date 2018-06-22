<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    PDOException,
    PDO;
/** ***********************************************************************************************
 * Class for managing fields types
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldsTypesManager
{
    private static
        $fieldsTypesId          = [],
        $fieldsTypesIdQueried   = false;
    /** **********************************************************************
     * get fields types ID array
     *
     * @return  array                       fields types ID array
     ************************************************************************/
    public static function getFieldsTypes() : array
    {
        if (self::$fieldsTypesIdQueried)
        {
            return self::$fieldsTypesId;
        }

        try
        {
            $preparedQuery = self::getPDO()->prepare('SELECT * FROM fields_types');
            $preparedQuery->execute();

            $queryResult = $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
            foreach ($queryResult as $item)
            {
                self::$fieldsTypesId[$item['CODE']] = $item['ID'];
            }
        }
        catch (PDOException $exception)
        {

        }

        self::$fieldsTypesIdQueried = true;
        return self::$fieldsTypesId;
    }
    /** **********************************************************************
     * get PDO connection
     *
     * @return  PDO                         PDO connection
     * @throws  PDOException                connection error
     ************************************************************************/
    private static function getPDO() : PDO
    {
        $host       = $GLOBALS['DB_HOST'];
        $name       = $GLOBALS['DB_NAME'];
        $login      = $GLOBALS['DB_LOGIN'];
        $password   = $GLOBALS['DB_PASSWORD'];

        try
        {
            return new PDO
            (
                "mysql:dbname=$name;host=$host",
                $login,
                $password
            );
        }
        catch (PDOException $exception)
        {
            throw $exception;
        }
    }
}