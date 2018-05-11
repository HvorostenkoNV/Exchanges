<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers\Data;

use
    UnitTests\Core\MapDataClass,
    Main\Helpers\Data\DBFieldsValues;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBFieldsValues class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBFieldsValuesTest extends MapDataClass
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return DBFieldsValues::class;
    }
    /** **********************************************************************
     * get correct data
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        return
        [
            'one'   => 'string',
            'two'   => '',
            'three' => 2,
            'four'  => 2.5,
            'five'  => 0,
            'six'   => null
        ];
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
            new DBFieldsValues,
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
}