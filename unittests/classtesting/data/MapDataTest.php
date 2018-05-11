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
    public static function getMapClassName() : string
    {
        return MapData::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        return
        [
            1       => 'string',
            2       => '',
            'three' => 2,
            'four'  => 2.5,
            5       => 0,
            6       => true,
            7       => false,
            8       => [1, 2, 3],
            9       => ['string', '', 2.5, 0, true, false],
            10      => [],
            11      => new MapData,
            12      => null
        ];
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     ************************************************************************/
    public static function getIncorrectDataKeys() : array
    {
        return
        [
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new MapData,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return [];
    }
}