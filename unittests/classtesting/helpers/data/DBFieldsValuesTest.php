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
    protected static function getMapClassName() : string
    {
        return DBFieldsValues::class;
    }
    /** **********************************************************************
     * get correct data
     * @return  array                       correct data array
     ************************************************************************/
    protected static function getCorrectData() : array
    {
        return
        [
            'One'   => 'string',
            'two'   => 1,
            'three' => 1.5,
            'four'  => null
        ];
    }
    /** **********************************************************************
     * get incorrect keys
     * @return  array                       incorrect keys
     ************************************************************************/
    protected static function getIncorrectKeys() : array
    {
        return
        [
            '',
            1,
            5.5,
            true,
            [1, 2, 3],
            new DBFieldsValues,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                       incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        return
        [
            true,
            [1, 2, 3],
            new DBFieldsValues
        ];
    }
}