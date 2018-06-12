<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers\Data;

use
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Helpers\Data\DBRow;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBRow class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBRowTest extends MapDataAbstractTest
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return DBRow::class;
    }
    /** **********************************************************************
     * get correct data
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result = [];
        $keys   = self::getCorrectDataKeys();
        $values = self::getCorrectDataValues();

        foreach ($keys as $key)
        {
            foreach ($values as $value)
            {
                $result[] = [$key, $value];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect keys
     * @return  array                       incorrect data keys
     ************************************************************************/
    public static function getIncorrectDataKeys() : array
    {
        return
        [
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new DBRow,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                       incorrect data values
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            []
        ];
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     ************************************************************************/
    private static function getCorrectDataKeys() : array
    {
        return
        [
            'string'
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     ************************************************************************/
    private static function getCorrectDataValues() : array
    {
        return
        [
            'string',
            '',
            2,
            2.5,
            0,
            null
        ];
    }
}