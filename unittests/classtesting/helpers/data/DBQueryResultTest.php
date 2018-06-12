<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers\Data;

use
    UnitTests\ClassTesting\Data\QueueDataAbstractTest,
    Main\Data\MapData,
    Main\Helpers\Data\DBRow,
    Main\Helpers\Data\DBQueryResult;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBQueryResult class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBQueryResultTest extends QueueDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    public static function getQueueClassName() : string
    {
        return DBQueryResult::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     * @throws
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        $result = [];

        for ($index = 1; $index <= 3; $index++)
        {
            $fieldsValues = new DBRow;
            $fieldsValues->set('field', 'value');
            $result[] = $fieldsValues;
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
            new DBQueryResult,
            new DBRow,
            new MapData,
            null
        ];
    }
}