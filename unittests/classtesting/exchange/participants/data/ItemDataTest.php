<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    Throwable,
    UnexpectedValueException,
    InvalidArgumentException,
    DomainException,
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantStub,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
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
        $participant            = new ParticipantStub;

        foreach ($availableFieldsTypes as $type)
        {
            for ($index = 1; $index <= 10; $index++)
            {
                $fieldParams    = new MapData;
                $itemValue      = null;

                while (empty($itemValue))
                {
                    $itemValue = FieldsTypesManager::getField($type)->getRandomValue();
                }

                $fieldParams->set('id',     rand(1, getrandmax()));
                $fieldParams->set('name',   "field-$type-$index");
                $fieldParams->set('type',   $type);

                $field      = new ParticipantField($participant, $fieldParams);
                $result[]   = [$field, $itemValue];
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
                'someString',
                '',
                15,
                0,
                1.5,
                true,
                false,
                null,
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
     * @return void
     * @throws
     ************************************************************************/
    public function setingIncorrectFieldValue() : void
    {
        $itemData       = new ItemData;
        $exceptionName  = InvalidArgumentException::class;

        foreach ($this->getCorrectParticipantsFields() as $field)
        {
            $incorrectValue = null;

            try
            {
                $incorrectValue = $this->getFieldIncorrectValue($field->getParam('type'));
            }
            catch (UnexpectedValueException $exception)
            {
                self::assertTrue(true);
                continue;
            }

            try
            {
                $itemData->set($field, $incorrectValue);
                self::fail("Expect \"$exceptionName\" on seting incorrect value");
            }
            catch (InvalidArgumentException $exception)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * get correct participants fields
     *
     * @return  ParticipantField[]          correct participants fields
     ************************************************************************/
    private function getCorrectParticipantsFields() : array
    {
        $result = [];

        try
        {
            foreach (self::getCorrectData() as $data)
            {
                $result[] = $data[0];
            }
        }
        catch (Throwable $exception)
        {

        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect value for field type
     *
     * @param   string $fieldType           field type
     * @return  mixed                       incorrect value
     * @throws  UnexpectedValueException    incorrect value was not found
     ************************************************************************/
    private function getFieldIncorrectValue(string $fieldType)
    {
        $fieldTypeObject    = null;
        $availableValues    =
            [
                'someString',
                '',
                15,
                0,
                1.5,
                true,
                false,
                null,
                [],
                new MapData
            ];

        try
        {
            $fieldTypeObject = FieldsTypesManager::getField($fieldType);
        }
        catch (InvalidArgumentException $exception)
        {
            throw new UnexpectedValueException;
        }

        foreach ($availableValues as $value)
        {
            try
            {
                $fieldTypeObject->validateValue($value);
            }
            catch (DomainException $exception)
            {
                return $value;
            }
        }

        throw new UnexpectedValueException;
    }
}