<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\MapDataClass,
    Main\Exchange\Participants\Data\Field,
    Main\Exchange\Participants\Data\FieldsMap;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\FieldsMap class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsMapTest extends MapDataClass
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    protected static function getMapClassName() : string
    {
        return FieldsMap::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    protected static function getCorrectData() : array
    {
        $result = [];

        for ($index = 1; $index <= 10; $index++)
        {
            $field = new Field;
            $field->setName('field'.$index);
            $result['field'.$index] = $field;
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect keys
     ************************************************************************/
    protected static function getIncorrectKeys() : array
    {
        return
        [
            '',
            'someField',
            1,
            5.5,
            true,
            [1, 2, 3],
            new Field,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        return
        [
            '',
            'someField',
            1,
            5.5,
            true,
            [1, 2, 3],
            new Field,
            null
        ];
    }
}