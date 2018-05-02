<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Data;

use
    UnitTests\Core\MapDataClass,
    Main\Data\MapData;
/** ***********************************************************************************************
 * Test Main\Data\MapData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MapDataTest extends MapDataClass
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    protected static function getMapClassName() : string
    {
        return MapData::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    protected static function getCorrectData() : array
    {
        return
        [
            1       => 'string',
            'two'   => 2,
            'three' => 2.5,
            4       => true,
            5       => [1, 2, 3],
            6       => new MapData,
            7       => null
        ];
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect keys
     ************************************************************************/
    protected static function getIncorrectKeys() : array
    {
        return
        [
            [1, 2, 3],
            new MapData,
            true,
            5.5,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        return [];
    }
}