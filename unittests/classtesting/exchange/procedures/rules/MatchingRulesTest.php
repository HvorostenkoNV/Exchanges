<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Rules;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\Users1C      as Users1CParticipant,
    Main\Exchange\Participants\UsersAD      as UsersADParticipant,
    Main\Exchange\Participants\UsersBitrix  as UsersBitrixParticipant,
    Main\Exchange\Procedures\Rules\MatchingRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\MatchingRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchingRulesTest extends ExchangeTestCase
{
    /** **********************************************************************
     * testing aliases read/write operations
     *
     * @test
     ************************************************************************/
    public function aliasesReadWriteOperations() : void
    {
        $matchingRules  = new MatchingRules;
        $fieldAlias     = 'fieldAlias';

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $participant)
        {
            $fieldName = get_class($participant).'Field';

            $matchingRules->attachFieldAlias($participant, $fieldName, $fieldAlias);
            self::assertEquals
            (
                $fieldName,
                $matchingRules->getFieldByAlias($participant, $fieldAlias),
                'Expect get field name by alias same as seted'
            );
        }
    }
    /** **********************************************************************
     * testing fields aliases drop operation
     *
     * @test
     * @depends aliasesReadWriteOperations
     ************************************************************************/
    public function aliasesDropOperation() : void
    {
        $matchingRules  = new MatchingRules;
        $participant    = new Users1CParticipant;
        $fieldAlias     = 'fieldAlias';

        $matchingRules->attachFieldAlias($participant, 'field', $fieldAlias);
        $matchingRules->dropFieldAlias($participant, $fieldAlias);

        self::assertNull
        (
            $matchingRules->getFieldByAlias($participant, $fieldAlias),
            'Expect null on getting field by alias after drop alias'
        );
    }
}