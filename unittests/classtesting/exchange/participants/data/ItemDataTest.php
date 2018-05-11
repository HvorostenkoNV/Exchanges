<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\MapDataClass,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Data\FieldValue,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ItemData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ItemDataTest extends MapDataClass
{
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return ItemData::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     * @throws
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result         = [];
        $fieldsTypes    = FieldsTypesManager::getAvailableFieldsTypes();

        foreach ($fieldsTypes as $type)
        {
            for ($index = 1; $index <= 10; $index++)
            {
                $fieldName  = 'field'.ucfirst($type).$index;
                $value      = FieldsTypesManager::getField($type)->getRandomValue();
                $params     = new MapData(['name' => $fieldName, 'type' => $type]);
                $field      = new Field($params);
                $fieldValue = new FieldValue($value, $field);

                $result[$fieldName] = $fieldValue;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     * @throws
     ************************************************************************/
    public static function getIncorrectDataKeys() : array
    {
        $unknownFieldName   = 'unknownFieldName';
        $correctData        = self::getCorrectData();
        $result             =
        [
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new ItemData,
            null
        ];

        while (array_key_exists($unknownFieldName, $correctData))
        {
            $unknownFieldName .= '!';
        }
        $result[] = $unknownFieldName;

        return $result;
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     * @throws
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        $unknownFieldName   = 'unknownFieldName';
        $correctData        = self::getCorrectData();
        $result             =
        [
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new ItemData,
            null
        ];

        while (array_key_exists($unknownFieldName, $correctData))
        {
            $unknownFieldName .= '!';
        }

        $value      = FieldsTypesManager::getField('string')->getRandomValue();
        $fieldName  = $unknownFieldName.'!';
        $params     = new MapData(['name' => $fieldName, 'type' => 'string']);
        $field      = new Field($params);
        $fieldValue = new FieldValue($value, $field);
        $result[$unknownFieldName] = $fieldValue;

        return $result;
    }
}