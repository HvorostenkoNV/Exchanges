<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers\Data;

use
    UnitTests\Core\QueueDataClass,
    Main\Exchange\Procedures\UsersExchange,
    Main\Exchange\Procedures\Data\ProceduresQueue;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Data\ProceduresQueue class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProceduresQueueTest extends QueueDataClass
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return ProceduresQueue::class;
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
            new UsersExchange
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
            new ProceduresQueue,
            null
        ];
    }
}