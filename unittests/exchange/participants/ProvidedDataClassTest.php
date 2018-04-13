<?php
declare(strict_types=1);

use
    Main\Data\MapData,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProvidedDataClassTest extends QueueDataClass
{
    protected static $queueClassName = ProvidedData::class;
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
            $fieldsValues = new ItemData;
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
            new ProvidedData,
            new MapData,
            new ItemData,
            NULL
        ];
    }
}