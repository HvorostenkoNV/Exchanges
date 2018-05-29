<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers\Data;

use
    UnitTests\Core\SetDataClass,
    Main\Exchange\Participants\Users1C      as Users1CParticipant,
    Main\Exchange\Participants\UsersAD      as UsersADParticipant,
    Main\Exchange\Participants\UsersBitrix  as UsersBitrixParticipant,
    Main\Exchange\Procedures\Data\ParticipantsSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Data\ParticipantsQueueTest class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ParticipantsSetTest extends SetDataClass
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return ParticipantsSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        return
        [
            new Users1CParticipant,
            new UsersADParticipant,
            new UsersBitrixParticipant
        ];
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
            new ParticipantsSet,
            null
        ];
    }
}