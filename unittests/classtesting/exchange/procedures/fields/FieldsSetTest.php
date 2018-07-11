<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantStub,
    UnitTests\ClassTesting\Exchange\Procedures\ProcedureStub,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet     as ParticipantFieldsSet,
    Main\Exchange\Procedures\Fields\Field           as ProcedureField,
    Main\Exchange\Procedures\Fields\FieldsSet       as ProcedureFieldsSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Fields\FieldsSet class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsSetTest extends SetDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return ProcedureFieldsSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        $result                 = [];
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();
        $participant            = new ParticipantStub;
        $procedure              = new ProcedureStub;

        for ($index = 1; $index <= 10; $index++)
        {
            $procedureFieldParams   = new MapData;
            $participantsFieldsSet  = new ParticipantFieldsSet;

            $procedureFieldParams->set('id', rand(1, getrandmax()));
            for ($index = 1; $index <= 10; $index++)
            {
                $participantFieldParams = new MapData;
                $participantFieldParams->set('id',     rand(1, getrandmax()));
                $participantFieldParams->set('name',   "fieldName-$index");
                $participantFieldParams->set('type',   $availableFieldsTypes[array_rand($availableFieldsTypes)]);

                $participantField = new ParticipantField($participant, $participantFieldParams);
                $participantsFieldsSet->push($participantField);
            }

            $result[] = new ProcedureField($procedure, $procedureFieldParams, $participantsFieldsSet);
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
                new ParticipantFieldsSet,
                new ProcedureFieldsSet
            ];
    }
}