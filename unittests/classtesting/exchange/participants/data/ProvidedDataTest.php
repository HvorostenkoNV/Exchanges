<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\QueueDataClass,
    Main\Data\MapData,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ProvidedData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProvidedDataTest extends QueueDataClass
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return ProvidedData::class;
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
            $itemData = new ItemData;

            foreach ($itemsCorrectData as $values)
            {
                $itemData->set($values[0], $values[1]);
            }

            $result[] = $itemData;
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
            new ProvidedData,
            new MapData,
            new ItemData,
            null
        ];
    }
}