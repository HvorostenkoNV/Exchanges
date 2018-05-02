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
    protected static $queueClassName = ProceduresQueue::class;
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        parent::getCorrectValues();

        return
        [
            new UsersExchange
        ];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                   incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        parent::getIncorrectValues();

        return
        [
            'string',
            1,
            1.5,
            true,
            [1, 2, 3],
            new ProceduresQueue,
            null
        ];
    }
}