<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors\Result;

use
    UnitTests\ClassTesting\Data\QueueDataAbstractTest,
    Main\Exchange\DataProcessors\Data\CombinedItem,
    Main\Exchange\DataProcessors\Results\CombinedData;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Result\CollectedData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CombinedDataTest extends QueueDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return CombinedData::class;
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
            new CombinedItem,
            new CombinedItem,
            new CombinedItem
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
            new CombinedData,
            null
        ];
    }
}