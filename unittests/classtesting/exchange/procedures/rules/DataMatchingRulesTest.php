<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Data\MapData,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\FieldsSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\DataMatchingRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DataMatchingRulesTest extends MapDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return DataMatchingRules::class;
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
            [new ParticipantsSet, new FieldsSet],
            [new ParticipantsSet, new FieldsSet],
            [new ParticipantsSet, new FieldsSet]
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
            new MapData,
            new FieldsSet,
            new DataMatchingRules
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            new MapData,
            new ParticipantsSet,
            new DataMatchingRules
        ];
    }
}