<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Data\MapData,
    Main\Exchange\Procedures\Fields\ParticipantField,
    Main\Exchange\Procedures\Rules\DataCombiningRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\DataCombiningRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DataCombiningRulesTest extends MapDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return DataCombiningRules::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result = [];

        foreach (ParticipantFieldTest::getParamsForFieldConstruct() as $arrayInfo)
        {
            $participant        = $arrayInfo[0];
            $field              = $arrayInfo[1];
            $participantField   = new ParticipantField($participant, $field);

            $result[] = [$participantField, rand(0, 100)];
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
        return
        [
            'string',
            '',
            2.5,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new MapData,
            null
        ];
    }
}