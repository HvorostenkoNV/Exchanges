<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Rules;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\Users1C      as Users1CParticipant,
    Main\Exchange\Participants\UsersAD      as UsersADParticipant,
    Main\Exchange\Participants\UsersBitrix  as UsersBitrixParticipant,
    Main\Exchange\Procedures\Rules\CombiningRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\MatchingRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CombiningRulesTest extends ExchangeTestCase
{
    /** **********************************************************************
     * testing priority read/write operations
     *
     * @test
     * @throws
     ************************************************************************/
    public function priorityReadWriteOperations() : void
    {
        $combiningRules = new CombiningRules;

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $index => $participant)
        {
            $fieldName      = get_class($participant).'Field';
            $fieldPriority  = $index + 1;

            $combiningRules->attachFieldPriority($participant, $fieldName, $fieldPriority);
            self::assertEquals
            (
                $fieldPriority,
                $combiningRules->getFieldPriority($participant, $fieldName),
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
        $combiningRules = new CombiningRules;
        $participant    = new Users1CParticipant;
        $fieldName      = 'someField';

        $combiningRules->attachFieldPriority($participant, $fieldName, 10);
        $combiningRules->dropFieldPriority($participant, $fieldName);

        self::assertEquals
        (
            0,
            $combiningRules->getFieldPriority($participant, $fieldName),
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
        $combiningRules = new CombiningRules;

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $index => $participant)
        {
            $fieldName = get_class($participant).'Field';

            $combiningRules->attachEmptyFieldHasPriority($participant, $fieldName);
            self::assertTrue
            (
                $combiningRules->checkEmptyFieldHasPriority($participant, $fieldName),
                'Expect true on checking field has priority even if empty after seting true'
            );

            $combiningRules->detachEmptyFieldHasPriority($participant, $fieldName);
            self::assertFalse
            (
                $combiningRules->checkEmptyFieldHasPriority($participant, $fieldName),
                'Expect false on checking field has priority even if empty after seting false'
            );
        }
    }
}