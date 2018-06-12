<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    InvalidArgumentException,
    ReflectionClass,
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    UnitTests\ClassTesting\Exchange\Participants\FieldsTypes\ParticipantFieldClass  as FieldTypeTest,
    UnitTests\ClassTesting\Exchange\Participants\FieldsTypes\StringFieldTest        as RandomFieldTypeTest,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager                                  as FieldsTypesManager,
    Main\Exchange\Participants\FieldsTypes\Field                                    as FieldType,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ItemData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ItemDataTest extends MapDataAbstractTest
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
        $result                 = [];
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();

        foreach ($availableFieldsTypes as $type)
        {
            for ($index = 1; $index <= 10; $index++)
            {
                $params     = new MapData;
                $fieldName  = 'field'.ucfirst($type).$index;
                $value      = FieldsTypesManager::getField($type)->getRandomValue();

                $params->set('name', $fieldName);
                $params->set('type', $type);

                $result[] = [new Field($params), $value];
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
        return
        [
            new MapData,
            new ItemData
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     * @throws
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return [];
    }
    /** **********************************************************************
     * check seting incorrect field value
     *
     * @test
     * @throws
     ************************************************************************/
    public function setingIncorrectFieldValue() : void
    {
        $itemData               = new ItemData;
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();
        $exceptionName          = InvalidArgumentException::class;

        foreach ($availableFieldsTypes as $fieldType)
        {
            $fieldTypeObject    = FieldsTypesManager::getField($fieldType);
            $incorrectValues    = $this->getFieldIncorrectValues($fieldTypeObject);
            $fieldParams        = new MapData;

            $fieldParams->set('name', 'fieldName');
            $fieldParams->set('type', $fieldType);
            $field = new Field($fieldParams);

            foreach ($incorrectValues as $value)
            {
                try
                {
                    $itemData->set($field, $value);
                    self::fail("Expect \"$exceptionName\" on seting field with incorrect value");
                }
                catch (InvalidArgumentException $exception)
                {
                    self::assertTrue(true);
                }
            }
        }
    }
    /** **********************************************************************
     * get incorrect values for field type
     *
     * @param   FieldType   $fieldType      field type
     * @return  array                       array of incorrect values
     ************************************************************************/
    private function getFieldIncorrectValues(FieldType $fieldType) : array
    {
        $result = [];

        $testClass  = $this->getParticipantFieldTestClass($fieldType);
        $values     = $testClass::getValuesForValidation();

        foreach ($values as $valuesArray)
        {
            if (count($valuesArray) === 1)
            {
                $result[] = $valuesArray[0];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participant field test class
     *
     * @param   FieldType   $fieldType      field type
     * @return  FieldTypeTest               field type test
     ************************************************************************/
    private function getParticipantFieldTestClass(FieldType $fieldType) : FieldTypeTest
    {
        $fieldReflection        = new ReflectionClass($fieldType);
        $fieldTestReflection    = new ReflectionClass(RandomFieldTypeTest::class);
        $fieldTypeTest          = $fieldTestReflection->getNamespaceName().'\\'.$fieldReflection->getShortName().'Test';

        return new $fieldTypeTest;
    }
}