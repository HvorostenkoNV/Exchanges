<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Data;

use Main\Data\QueueData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class QueueDataTest extends QueueDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return QueueData::class;
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
            new QueueData,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return [];
    }
}