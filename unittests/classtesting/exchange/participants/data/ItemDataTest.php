<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\MapDataClass,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ItemData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ItemDataTest extends MapDataClass
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    protected static function getMapClassName() : string
    {
        return ItemData::class;
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
            'string'            => 'string',
            'integer'           => 1,
            'boolean'           => true,
            'arrayOfStrings'    => ['string',   'string',   'string'],
            'arrayOfIntegers'   => [1,      2,      3],
            'arrayOfBooleans'   => [true,   true,   false]
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
            '',
            1,
            5.5,
            true,
            [1, 2, 3],
            new ItemData,
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
        return
        [
            new ItemData,
            null,
            [1,         2,          true],
            ['string',  'string',   2.5],
        ];
    }
}