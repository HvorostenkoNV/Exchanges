<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    DomainException,
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\FieldsTypes\Field;
/** ***********************************************************************************************
 * Parent class for testing Main\Exchange\Participants\FieldsTypes\Field classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class ParticipantFieldClass extends ExchangeTestCase
{
    /** **********************************************************************
     * get field class name
     *
     * @return  string                      field class name
     ************************************************************************/
    abstract public static function getFieldClassName() : string;
    /** **********************************************************************
     * get values for validation
     *
     * @return  array                       values for validation
     * @example
     *                                      [value, expected value]
     *                                      [value] - if no expected value? expect exception
     ************************************************************************/
    abstract public static function getValuesForValidation() : array;
    /** **********************************************************************
     * get values for print converting
     *
     * @return  array                       values for print converting
     * @example
     *                                      [value, expected value]
     *                                      [value] - if no expected value? expect exception
     ************************************************************************/
    abstract public static function getValuesForPrintConverting() : array;
    /** **********************************************************************
     * check validation value process
     *
     * @test
     * @throws
     ************************************************************************/
    public function validationValueProcess() : void
    {
        $field                  = $this->createFieldObject();
        $className              = static::getFieldClassName();
        $valuesForValidation    = static::getValuesForValidation();

        if (count($valuesForValidation) <= 0)
        {
            self::markTestSkipped("no \"values for validation\" for testing \"$className\" class");
        }

        foreach ($valuesForValidation as $values)
        {
            $value              = $values[0];
            $hasExpectedValue   = array_key_exists(1, $values);
            $expectedValue      = $hasExpectedValue ? $values[1] : null;
            $getedValue         = null;
            $exceptionCaught    = false;
            $exceptionName      = DomainException::class;

            try
            {
                $getedValue = $field->validateValue($value);
            }
            catch (DomainException $exception)
            {
                $exceptionCaught = true;
            }

            if ($hasExpectedValue)
            {
                self::assertEquals
                (
                    $expectedValue,
                    $getedValue,
                    "Validated value not equals expected by field type \"$className\""
                );
            }
            else
            {
                self::assertTrue
                (
                    $exceptionCaught,
                    "Expect $exceptionName on seting invalid value for validating by field type \"$className\""
                );
            }
        }
    }
    /** **********************************************************************
     * check converting value for print process
     *
     * @test
     * @throws
     ************************************************************************/
    public function convertingValueForPrintProcess() : void
    {
        $field                  = $this->createFieldObject();
        $className              = static::getFieldClassName();
        $valuesForConverting    = static::getValuesForPrintConverting();

        if (count($valuesForConverting) <= 0)
        {
            self::markTestSkipped("no \"values for converting for print\" for testing \"$className\" class");
        }

        foreach ($valuesForConverting as $values)
        {
            $value              = $values[0];
            $hasExpectedValue   = array_key_exists(1, $values);
            $expectedValue      = $hasExpectedValue ? $values[1] : null;
            $getedValue         = null;
            $exceptionCaught    = false;
            $exceptionName      = DomainException::class;

            try
            {
                $getedValue = $field->convertValueForPrint($value);
            }
            catch (DomainException $exception)
            {
                $exceptionCaught = true;
            }

            if ($hasExpectedValue)
            {
                self::assertEquals
                (
                    $expectedValue,
                    $getedValue,
                    "Converting value for print not equals expected by field type \"$className\""
                );
            }
            else
            {
                self::assertTrue
                (
                    $exceptionCaught,
                    "Expect $exceptionName on seting invalid value for converting for print by field type \"$className\""
                );
            }
        }
    }
    /** **********************************************************************
     * check random value
     *
     * @test
     * @throws
     ************************************************************************/
    public function randomValue() : void
    {
        $field              = $this->createFieldObject();
        $className          = static::getFieldClassName();
        $randomValue        = $field->getRandomValue();
        $validatedValue     = null;
        $exceptionCaught    = false;

        try
        {
            $validatedValue = $field->validateValue($randomValue);
        }
        catch (DomainException $exception)
        {
            $exceptionCaught = true;
        }

        self::assertEquals
        (
            $randomValue,
            $validatedValue,
            "Expect validation process gives no result on random value from \"$className\""
        );
        self::assertFalse
        (
            $exceptionCaught,
            "Expect no exception on validating random value in \"$className\""
        );
    }
    /** **********************************************************************
     * get new participant field object
     *
     * @return  Field                       new field object
     ************************************************************************/
    private function createFieldObject() : Field
    {
        $className = static::getFieldClassName();
        return new $className;
    }
}