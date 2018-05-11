<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    UnitTests\Core\MapDataClass,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Fields\FieldsMap;
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
    public static function getMapClassName() : string
    {
        return FieldsMap::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     * @throws
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result = [];

        foreach (FieldsTypesManager::getAvailableFieldsTypes() as $type)
        {
            $fieldName  = 'field'.$type;
            $fieldParam = new MapData(['name' => $fieldName, 'type' => $type]);
            $result[$fieldName] = new Field($fieldParam);
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     ************************************************************************/
    public static function getIncorrectDataKeys() : array
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
            new MapData,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
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
            new MapData,
            null
        ];
    }
}