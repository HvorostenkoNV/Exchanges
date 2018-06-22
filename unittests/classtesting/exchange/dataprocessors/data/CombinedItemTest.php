<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors\Data;

use
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Exchange\Procedures\Fields\ProcedureField,
    Main\Exchange\DataProcessors\Data\CombinedItem;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Data\CombinedItem class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CombinedItemTest extends MapDataAbstractTest
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return CombinedItem::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result = [];

        foreach (self::getCorrectDataValues() as $value)
        {
            $result[] = [new ProcedureField, $value];
        }

        return $result;
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
            'string',
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new CombinedItem,
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
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       correct data values
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
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new CombinedItem,
            null
        ];
    }
}