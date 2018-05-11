<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\QueueDataClass,
    Main\Data\MapData,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\DataForDelivery class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DataForDeliveryTest extends QueueDataClass
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return DataForDelivery::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     * @throws
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        $result             = [];
        $itemsCorrectData   = ItemDataTest::getCorrectData();

        for ($index = 1; $index <= 10; $index++)
        {
            $result[] = new ItemData($itemsCorrectData);
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
            new DataForDelivery,
            new MapData,
            new ItemData,
            null
        ];
    }
}