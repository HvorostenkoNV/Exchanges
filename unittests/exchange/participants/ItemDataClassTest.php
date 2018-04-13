<?php
declare(strict_types=1);

use Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ItemData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ItemDataClassTest extends MapDataClass
{
    protected static $mapClassName = ItemData::class;
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectData() : array
    {
        parent::getCorrectData();

        return
        [
            'string'            => 'string',
            'integer'           => 1,
            'float'             => 1.5,
            'boolean'           => true,
            'arrayOfStrings'    => ['string',   'string',   'string'],
            'arrayOfIntegers'   => [1,          2,          3],
            'arrayOfFloats'     => [1.5,        2.5,        3.5],
            'arrayOfBooleans'   => [true,       true,       false]
        ];
    }
    /** **********************************************************************
     * get incorrect keys
     * @return  array                   incorrect keys
     ************************************************************************/
    protected static function getIncorrectKeys() : array
    {
        parent::getIncorrectKeys();

        return
        [
            '',
            1,
            5.5,
            true,
            [1, 2, 3],
            new ItemData,
            NULL
        ];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                   incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        parent::getIncorrectValues();

        return
        [
            new ItemData,
            NULL,
            [1,         2,          true],
            ['string',  'string',   2.5],
        ];
    }
}