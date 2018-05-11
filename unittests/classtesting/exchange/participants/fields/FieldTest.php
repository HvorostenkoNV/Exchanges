<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    Throwable,
    InvalidArgumentException,
    UnitTests\Core\ExchangeTestCase,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Fields\Field classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check creating object
     *
     * @test
     * @throws
     ************************************************************************/
    public function creatingObject() : void
    {
        $correctFieldParams     = $this->getCorrectData();
        $incorrectFieldParams   = $this->getIncorrectData();

        foreach ($correctFieldParams as $params)
        {
            try
            {
                new ParticipantField(new MapData($params));
                self::assertTrue(true);
            }
            catch (Throwable $exception)
            {
                $error = $exception->getMessage();
                self::fail("Error on creating new participant field: $error");
            }
        }

        foreach ($incorrectFieldParams as $params)
        {
            try
            {
                $exceptionName = InvalidArgumentException::class;
                new ParticipantField(new MapData($params));
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
     * @depends creatingObject
     * @throws
     ************************************************************************/
    public function paramsReading() : void
    {
        $correctFieldParams = $this->getCorrectData();

        foreach ($correctFieldParams as $params)
        {
            $field = new ParticipantField(new MapData($params));
            foreach ($params as $key => $value)
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
                'required'  => rand(0, 1) === 0
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
                'required'  => rand(0, 1) === 0
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
            'required'  => rand(0, 1) === 0
        ];

        return $result;
    }
}