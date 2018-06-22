<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    Throwable,
    InvalidArgumentException,
    UnitTests\AbstractTestCase,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Fields\Field classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldTest extends AbstractTestCase
{
    /** **********************************************************************
     * check correct creating object
     *
     * @test
     * @throws
     ************************************************************************/
    public function correctCreatingObject() : void
    {
        foreach ($this->getCorrectData() as $values)
        {
            try
            {
                $params = new MapData;

                foreach ($values as $key => $value)
                {
                    $params->set($key, $value);
                }

                new ParticipantField($params);
                self::assertTrue(true);
            }
            catch (Throwable $exception)
            {
                $error = $exception->getMessage();
                self::fail("Error on creating new participant field: $error");
            }
        }
    }
    /** **********************************************************************
     * check incorrect creating object
     *
     * @test
     * @throws
     ************************************************************************/
    public function incorrectCreatingObject() : void
    {
        $exceptionName = InvalidArgumentException::class;

        foreach ($this->getIncorrectData() as $values)
        {
            try
            {
                $params = new MapData;

                foreach ($values as $key => $value)
                {
                    $params->set($key, $value);
                }

                new ParticipantField($params);
                self::fail("Expect \"$exceptionName\" on creating new participant field with incorrect params");
            }
            catch (InvalidArgumentException $exception)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * check field params reading operations
     *
     * @test
     * @depends correctCreatingObject
     * @throws
     ************************************************************************/
    public function paramsReading() : void
    {
        foreach ($this->getCorrectData() as $values)
        {
            $params = new MapData;

            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            $field = new ParticipantField($params);
            foreach ($values as $key => $value)
            {
                self::assertEquals
                (
                    $value,
                    $field->getParam($key),
                    "Geted \"$key\" param not equals seted before"
                );
            }
        }
    }
    /** **********************************************************************
     * check getting field type operation
     *
     * @test
     * @depends correctCreatingObject
     * @throws
     ************************************************************************/
    public function gettingFieldType() : void
    {
        foreach ($this->getCorrectData() as $values)
        {
            $params     = new MapData;
            $fieldType  = $values['type'];

            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            $participantField = new ParticipantField($params);

            self::assertEquals
            (
                FieldsTypesManager::getField($fieldType),
                $participantField->getFieldType(),
                'Expect get same field type object as was seted by type'
            );
        }
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data
     ************************************************************************/
    private function getCorrectData() : array
    {
        $result = [];

        foreach (FieldsTypesManager::getAvailableFieldsTypes() as $type)
        {
            $result[] =
            [
                'type'      => $type,
                'name'      => 'someField'.$type,
                'required'  => rand(1, 2) == 2
            ];
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data
     ************************************************************************/
    private function getIncorrectData() : array
    {
        $result                     = [];
        $fieldsTypes                = FieldsTypesManager::getAvailableFieldsTypes();
        $incorrectFieldType         = 'incorrectFieldType';
        $incorrectFieldNames        =
        [
            '',
            2,
            2.5,
            0,
            true,
            false,
            [],
            new MapData,
            null
        ];
        $incorrectRequiredValues    =
        [
            'true',
            '',
            2,
            2.5,
            0,
            [],
            new MapData,
            null
        ];

        while (in_array($incorrectFieldType, $fieldsTypes))
        {
            $incorrectFieldType .= '!';
        }

        foreach ($incorrectFieldNames as $incorrectName)
        {
            $result[] =
            [
                'type'      => $fieldsTypes[array_rand($fieldsTypes)],
                'name'      => $incorrectName,
                'required'  => rand(1, 2) == 2
            ];
        }
        foreach ($incorrectRequiredValues as $incorrectRequiredValue)
        {
            $result[] =
            [
                'type'      => $fieldsTypes[array_rand($fieldsTypes)],
                'name'      => 'someName',
                'required'  => $incorrectRequiredValue
            ];
        }
        $result[] =
        [
            'type'      => $incorrectFieldType,
            'name'      => 'someName',
            'required'  => rand(1, 2) == 2
        ];

        return $result;
    }
}