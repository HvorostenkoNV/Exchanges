<?php
declare(strict_types=1);

use
    Main\Data\MapData,
    Main\Helpers\Data\DBFieldsValues,
    Main\Helpers\Data\DBQueryResult;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBQueryResult class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBQueryResultClassTest extends QueueDataClass
{
    protected static $queueClassName = DBQueryResult::class;
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     * @throws
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        parent::getCorrectValues();

        $result = [];

        for ($index = 1; $index <= 3; $index++)
        {
            $fieldsValues = new DBFieldsValues;
            $fieldsValues->set('field', 'value');
            $result[] = $fieldsValues;
        }

        return $result;
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
            new DBQueryResult,
            new DBFieldsValues,
            new MapData,
            NULL
        ];
    }
}