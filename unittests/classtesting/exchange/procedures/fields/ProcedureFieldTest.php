<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    Main\Data\MapData,
    Main\Exchange\Procedures\Fields\ParticipantField,
    Main\Exchange\Procedures\Fields\ProcedureField;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Fields\ProcedureField class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProcedureFieldTest extends SetDataAbstractTest
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return ProcedureField::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        $result                 = [];
        $dataForFieldsConstruct = ParticipantFieldTest::getParamsForFieldConstruct();

        foreach ($dataForFieldsConstruct as $arrayInfo)
        {
            $participant        = $arrayInfo[0];
            $field              = $arrayInfo[1];
            $participantField   = new ParticipantField($participant, $field);

            $result[] = $participantField;
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            new MapData,
            new ProcedureField
        ];
    }
}