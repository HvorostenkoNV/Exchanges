<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Rules;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\Users1C      as Users1CParticipant,
    Main\Exchange\Participants\UsersAD      as UsersADParticipant,
    Main\Exchange\Participants\UsersBitrix  as UsersBitrixParticipant,
    Main\Exchange\Procedures\Rules\DataCombiningRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\DataCombiningRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DataCombiningRulesTest extends ExchangeTestCase
{
    /** **********************************************************************
     * testing priority read/write operations
     *
     * @test
     * @throws
     ************************************************************************/
    public function priorityReadWriteOperations() : void
    {
        $rules = new DataCombiningRules;

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $index => $participant)
        {
            $fieldName      = get_class($participant).'Field';
            $fieldPriority  = $index + 1;

            $rules->attachFieldPriority($participant, $fieldName, $fieldPriority);
            self::assertEquals
            (
                $fieldPriority,
                $rules->getFieldPriority($participant, $fieldName),
                'Expect get field priority as seted'
            );
        }
    }
    /** **********************************************************************
     * testing fields priority drop operation
     *
     * @test
     * @depends priorityReadWriteOperations
     * @throws
     ************************************************************************/
    public function priorityDropOperation() : void
    {
        $rules          = new DataCombiningRules;
        $participant    = new Users1CParticipant;
        $fieldName      = 'someField';

        $rules->attachFieldPriority($participant, $fieldName, 10);
        $rules->dropFieldPriority($participant, $fieldName);

        self::assertEquals
        (
            0,
            $rules->getFieldPriority($participant, $fieldName),
            'Expect null on getting field priority after drop priority'
        );
    }
    /** **********************************************************************
     * testing empty priority read/write operations
     *
     * @test
     * @throws
     ************************************************************************/
    public function emptyPriorityReadWriteOperations() : void
    {
        $rules = new DataCombiningRules;

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $index => $participant)
        {
            $fieldName = get_class($participant).'Field';

            $rules->attachEmptyFieldHasPriority($participant, $fieldName);
            self::assertTrue
            (
                $rules->checkEmptyFieldHasPriority($participant, $fieldName),
                'Expect true on checking field has priority even if empty after seting true'
            );

            $rules->detachEmptyFieldHasPriority($participant, $fieldName);
            self::assertFalse
            (
                $rules->checkEmptyFieldHasPriority($participant, $fieldName),
                'Expect false on checking field has priority even if empty after seting false'
            );
        }
    }
}