<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    InvalidArgumentException,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantStub,
    UnitTests\ClassTesting\Exchange\Procedures\ProcedureStub,
    UnitTests\AbstractTestCase,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet     as ParticipantsFieldsSet,
    Main\Exchange\Procedures\Fields\Field           as ProcedureField;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Fields\Field class
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
     * @return void
     * @throws
     ************************************************************************/
    public function creating() : void
    {
        $procedure          = new ProcedureStub;
        $participantsFields = $this->getCorrectParticipantsFieldsSet();

        foreach ($this->getFieldCorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            try
            {
                new ProcedureField($procedure, $params, $participantsFields);
                self::assertTrue(true);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                self::fail("Error on creating new procedure field: $error");
            }
        }
    }
    /** **********************************************************************
     * check incorrect creating field
     *
     * @test
     * @depends creating
     * @return  void
     * @throws
     ************************************************************************/
    public function incorrectCreating() : void
    {
        $procedure                  = new ProcedureStub;
        $exceptionName              = InvalidArgumentException::class;
        $participantsFields         = $this->getCorrectParticipantsFieldsSet();
        $emptyParticipantsFields    = new ParticipantsFieldsSet;
        $correctFieldParams         = new MapData;

        foreach ($this->getFieldCorrectParams()[0] as $key => $value)
        {
            $correctFieldParams->set($key, $value);
        }

        foreach ($this->getFieldIncorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            try
            {
                new ProcedureField($procedure, $params, $participantsFields);
                self::fail("Expect \"$exceptionName\" on creating new procedure field with incorrect params");
            }
            catch (InvalidArgumentException $exception)
            {
                self::assertTrue(true);
            }
        }

        try
        {
            new ProcedureField($procedure, $correctFieldParams, $emptyParticipantsFields);
            self::fail("Expect \"$exceptionName\" on creating new procedure field with empty participants fields set");
        }
        catch (InvalidArgumentException $exception)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check field reading params operations
     *
     * @test
     * @depends creating
     * @return  void
     * @throws
     ************************************************************************/
    public function readingParams() : void
    {
        $procedure          = new ProcedureStub;
        $participantsFields = $this->getCorrectParticipantsFieldsSet();

        foreach ($this->getFieldCorrectParams() as $values)
        {
            $params = new MapData;
            foreach ($values as $key => $value)
            {
                $params->set($key, $value);
            }

            $field = new ProcedureField($procedure, $params, $participantsFields);
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
     * check getting procedure operation
     *
     * @test
     * @depends creating
     * @return  void
     * @throws
     ************************************************************************/
    public function gettingProcedure() : void
    {
        $procedure          = new ProcedureStub;
        $participantsFields = $this->getCorrectParticipantsFieldsSet();
        $paramsValues       = $this->getFieldCorrectParams()[0];
        $params             = new MapData;

        foreach ($paramsValues as $key => $value)
        {
            $params->set($key, $value);
        }

        $field = new ProcedureField($procedure, $params, $participantsFields);
        self::assertEquals
        (
            $procedure,
            $field->getProcedure(),
            'Expect get same procedure as was seted'
        );
    }
    /** **********************************************************************
     * check getting participants fields operation
     *
     * @test
     * @depends creating
     * @return  void
     * @throws
     ************************************************************************/
    public function gettingParticipantsFields() : void
    {
        $procedure          = new ProcedureStub;
        $participantsFields = $this->getCorrectParticipantsFieldsSet();
        $paramsValues       = $this->getFieldCorrectParams()[0];
        $params             = new MapData;

        foreach ($paramsValues as $key => $value)
        {
            $params->set($key, $value);
        }

        $field = new ProcedureField($procedure, $params, $participantsFields);
        self::assertEquals
        (
            $participantsFields,
            $field->getParticipantsFields(),
            'Expect get same procedure as was seted'
        );
    }
    /** **********************************************************************
     * check getting rewound participants fields set
     *
     * @test
     * @depends creating
     * @return  void
     * @throws
     ************************************************************************/
    public function gettingRewoundParticipantsFieldsSet() : void
    {
        $procedure          = new ProcedureStub;
        $participantsFields = $this->getCorrectParticipantsFieldsSet();
        $paramsValues       = $this->getFieldCorrectParams()[0];
        $params             = new MapData;

        foreach ($paramsValues as $key => $value)
        {
            $params->set($key, $value);
        }

        while ($participantsFields->valid())
        {
            $participantsFields->next();
        }

        $field = new ProcedureField($procedure, $params, $participantsFields);
        self::assertEquals
        (
            0,
            $field->getParticipantsFields()->key(),
            'Expect get rewound participants fields set'
        );
    }
    /** **********************************************************************
     * get field correct params
     *
     * @return  array                       field correct params
     ************************************************************************/
    private function getFieldCorrectParams() : array
    {
        $result     = [];
        $idParams   = $this->getFieldCorrectIdParams();

        foreach ($idParams as $id)
        {
            $result[] = ['id' => $id];
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
     * get field incorrect params
     *
     * @return  array                       field incorrect params
     ************************************************************************/
    private function getFieldIncorrectParams() : array
    {
        $result     = [];
        $idParams   = $this->getFieldIncorrectIdParams();

        foreach ($idParams as $id)
        {
            $result[] = ['id' => $id];
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
     * get correct participants fields set
     *
     * @return  ParticipantsFieldsSet       correct participants fields set
     ************************************************************************/
    private function getCorrectParticipantsFieldsSet() : ParticipantsFieldsSet
    {
        $result                 = new ParticipantsFieldsSet;
        $participant            = new ParticipantStub;
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();
        $setSize                = rand(1, 10);

        for ($index = 0; $index <= $setSize; $index++)
        {
            $fieldParams = new MapData;
            $fieldParams->set('id',     rand(1, getrandmax()));
            $fieldParams->set('name',   "field-$index");
            $fieldParams->set('type',   $availableFieldsTypes[array_rand($availableFieldsTypes)]);

            try
            {
                $field = new ParticipantField($participant, $fieldParams);
                $result->push($field);
            }
            catch (InvalidArgumentException $exception)
            {

            }
        }

        return $result;
    }
}