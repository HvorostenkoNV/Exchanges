<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    InvalidArgumentException,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantStub,
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
     * check correct creating field
     *
     * @test
     * @throws
     ************************************************************************/
    public function creating() : void
    {
        $participant = new ParticipantStub;

        foreach ($this->getFieldCorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            try
            {
                new ParticipantField($participant, $params);
                self::assertTrue(true);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                self::fail("Error on creating new participant field: $error");
            }
        }
    }
    /** **********************************************************************
     * check incorrect creating field
     *
     * @test
     * @depends creatingObject
     * @throws
     ************************************************************************/
    public function incorrectCreating() : void
    {
        $participant    = new ParticipantStub;
        $exceptionName  = InvalidArgumentException::class;

        foreach ($this->getFieldIncorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            try
            {
                new ParticipantField($participant, $params);
                self::fail("Expect \"$exceptionName\" on creating new participant field with incorrect params");
            }
            catch (InvalidArgumentException $exception)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * check field reading params operations
     *
     * @test
     * @depends creating
     * @throws
     ************************************************************************/
    public function readingParams() : void
    {
        $participant = new ParticipantStub;

        foreach ($this->getFieldCorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            $field = new ParticipantField($participant, $params);
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
     * check getting participant operation
     *
     * @test
     * @depends creating
     * @throws
     ************************************************************************/
    public function gettingParticipant() : void
    {
        $participant    = new ParticipantStub;
        $paramsValues   = $this->getFieldCorrectParams()[0];
        $params         = new MapData;

        foreach ($paramsValues as $key => $value)
        {
            $params->set($key, $value);
        }

        $field = new ParticipantField($participant, $params);
        self::assertEquals
        (
            $participant,
            $field->getParticipant(),
            'Expect get same participant as was seted'
        );
    }
    /** **********************************************************************
     * check getting field type operation
     *
     * @test
     * @depends creating
     * @throws
     ************************************************************************/
    public function gettingFieldType() : void
    {
        $participant = new ParticipantStub;

        foreach ($this->getFieldCorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            $field = new ParticipantField($participant, $params);
            self::assertEquals
            (
                FieldsTypesManager::getField($values['type']),
                $field->getFieldType(),
                'Expect get same field type object as was seted by type'
            );
        }
    }
    /** **********************************************************************
     * get field correct params
     *
     * @return  array                       field correct params
     ************************************************************************/
    private function getFieldCorrectParams() : array
    {
        $result         = [];
        $idParams       = $this->getFieldCorrectIdParams();
        $nameParams     = $this->getFieldCorrectNameParams();
        $typeParams     = $this->getFieldCorrectFieldsTypesParams();
        $requiredParams = $this->getFieldCorrectRequiredParams();

        while (count($typeParams) > 0)
        {
            $result[] =
                [
                    'id'        => array_pop($idParams),
                    'name'      => array_pop($nameParams),
                    'type'      => array_pop($typeParams),
                    'required'  => array_pop($requiredParams)
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * get field correct ID params
     *
     * @return  array                       field correct ID params
     ************************************************************************/
    private function getFieldCorrectIdParams() : array
    {
        $result = [];

        for ($index = 30; $index > 0; $index--)
        {
            $result[] = rand(1, getrandmax());
        }

        return $result;
    }
    /** **********************************************************************
     * get field correct name params
     *
     * @return  array                       field correct name params
     ************************************************************************/
    private function getFieldCorrectNameParams() : array
    {
        $result = [];

        for ($index = 30; $index > 0; $index--)
        {
            $result[] = 'some-field-name-'.rand(1, getrandmax());
        }

        return $result;
    }
    /** **********************************************************************
     * get field correct type params
     *
     * @return  array                       field correct type params
     ************************************************************************/
    private function getFieldCorrectFieldsTypesParams() : array
    {
        return FieldsTypesManager::getAvailableFieldsTypes();
    }
    /** **********************************************************************
     * get field correct required params
     *
     * @return  array                       field correct required params
     ************************************************************************/
    private function getFieldCorrectRequiredParams() : array
    {
        $result = [];

        for ($index = 30; $index > 0; $index--)
        {
            $result[] = rand(1, 2) == 2;
        }

        return $result;
    }
    /** **********************************************************************
     * get field incorrect params
     *
     * @return  array                       field incorrect params
     ************************************************************************/
    private function getFieldIncorrectParams() : array
    {
        $result                 = [];
        $correctIdParams        = $this->getFieldCorrectIdParams();
        $correctNameParams      = $this->getFieldCorrectNameParams();
        $correctTypeParams      = $this->getFieldCorrectFieldsTypesParams();
        $correctRequiredParams  = $this->getFieldCorrectRequiredParams();

        foreach ($this->getFieldIncorrectIdParams() as $id)
        {
            $result[] =
                [
                    'id'        => $id,
                    'name'      => $correctNameParams[array_rand($correctNameParams)],
                    'type'      => $correctTypeParams[array_rand($correctTypeParams)],
                    'required'  => $correctRequiredParams[array_rand($correctRequiredParams)]
                ];
        }
        foreach ($this->getFieldIncorrectNameParams() as $name)
        {
            $result[] =
                [
                    'id'        => $correctIdParams[array_rand($correctIdParams)],
                    'name'      => $name,
                    'type'      => $correctTypeParams[array_rand($correctTypeParams)],
                    'required'  => $correctRequiredParams[array_rand($correctRequiredParams)]
                ];
        }
        foreach ($this->getFieldIncorrectFieldsTypesParams() as $type)
        {
            $result[] =
                [
                    'id'        => $correctIdParams[array_rand($correctIdParams)],
                    'type'      => $correctNameParams[array_rand($correctNameParams)],
                    'name'      => $type,
                    'required'  => $correctRequiredParams[array_rand($correctRequiredParams)]
                ];
        }
        foreach ($this->getFieldIncorrectRequiredParams() as $required)
        {
            $result[] =
                [
                    'id'        => $correctIdParams[array_rand($correctIdParams)],
                    'type'      => $correctNameParams[array_rand($correctNameParams)],
                    'name'      => $correctTypeParams[array_rand($correctTypeParams)],
                    'required'  => $required
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * get field incorrect ID params
     *
     * @return  array                       field incorrect ID params
     ************************************************************************/
    private function getFieldIncorrectIdParams() : array
    {
        return
            [
                'someString',
                '',
                -15,
                0,
                1.5,
                0.0,
                -1.5,
                true,
                false,
                null,
                [],
                new MapData
            ];
    }
    /** **********************************************************************
     * get field incorrect name params
     *
     * @return  array                       field incorrect name params
     ************************************************************************/
    private function getFieldIncorrectNameParams() : array
    {
        return
            [
                '',
                15,
                0,
                -15,
                1.5,
                0.0,
                -1.5,
                true,
                false,
                null,
                [],
                new MapData
            ];
    }
    /** **********************************************************************
     * get field incorrect type params
     *
     * @return  array                       field incorrect type params
     ************************************************************************/
    private function getFieldIncorrectFieldsTypesParams() : array
    {
        $result =
            [
                '',
                15,
                0,
                -15,
                1.5,
                0.0,
                -1.5,
                true,
                false,
                null,
                [],
                new MapData
            ];

        $correctFieldsTypes = $this->getFieldCorrectFieldsTypesParams();
        for ($index = 30; $index > 0; $index--)
        {
            $incorrectType = 'incorrect-field-type'.rand(1, getrandmax());
            while (in_array($incorrectType, $correctFieldsTypes))
            {
                $incorrectType .= '!';
            }
            $result[] = $incorrectType;
        }

        return $result;
    }
    /** **********************************************************************
     * get field incorrect required params
     *
     * @return  array                       field incorrect required params
     ************************************************************************/
    private function getFieldIncorrectRequiredParams() : array
    {
        return
            [
                'someString',
                '',
                15,
                0,
                -15,
                1.5,
                0.0,
                -1.5,
                null,
                [],
                new MapData
            ];
    }
}