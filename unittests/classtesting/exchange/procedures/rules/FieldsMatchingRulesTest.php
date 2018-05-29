<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Rules;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\Users1C      as Users1CParticipant,
    Main\Exchange\Participants\UsersAD      as UsersADParticipant,
    Main\Exchange\Participants\UsersBitrix  as UsersBitrixParticipant,
    Main\Exchange\Procedures\Rules\FieldsMatchingRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\FieldsMatchingRules class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsMatchingRulesTest extends ExchangeTestCase
{
    /** **********************************************************************
     * testing aliases read/write operations
     *
     * @test
     * @throws
     ************************************************************************/
    public function aliasesReadWriteOperations() : void
    {
        $rules      = new FieldsMatchingRules;
        $fieldAlias = 'fieldAlias';

        foreach ([new Users1CParticipant, new UsersADParticipant, new UsersBitrixParticipant] as $participant)
        {
            $fieldName = get_class($participant).'Field';

            $rules->attachFieldAlias($participant, $fieldName, $fieldAlias);
            self::assertEquals
            (
                $fieldName,
                $rules->getFieldByAlias($participant, $fieldAlias),
                'Expect get field name by alias same as seted'
            );
        }
    }
    /** **********************************************************************
     * testing fields aliases drop operation
     *
     * @test
     * @depends aliasesReadWriteOperations
     * @throws
     ************************************************************************/
    public function aliasesDropOperation() : void
    {
        $rules          = new FieldsMatchingRules;
        $participant    = new Users1CParticipant;
        $fieldAlias     = 'fieldAlias';

        $rules->attachFieldAlias($participant, 'field', $fieldAlias);
        $rules->dropFieldAlias($participant, $fieldAlias);

        self::assertNull
        (
            $rules->getFieldByAlias($participant, $fieldAlias),
            'Expect null on getting field by alias after drop alias'
        );
    }
}