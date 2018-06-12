<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    DomainException,
    UnitTests\AbstractTestCase,
    Main\Exchange\Participants\FieldsTypes\Field;
/** ***********************************************************************************************
 * Parent class for testing Main\Exchange\Participants\FieldsTypes\Field classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class ParticipantFieldClass extends AbstractTestCase
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
    public function validationValue() : void
    {
        $field                  = $this->createFieldObject();
        $valuesForValidation    = static::getValuesForValidation();
        $fieldClassName         = static::getFieldClassName();
        $exceptionName          = DomainException::class;

        if (count($valuesForValidation) <= 0)
        {
            self::markTestSkipped("no \"values for validation\" for testing \"$fieldClassName\" class");
        }

        foreach ($valuesForValidation as $values)
        {
            $value                  = $values[0];
            $hasExpectedValue       = array_key_exists(1, $values);
            $expectedValue          = $hasExpectedValue ? $values[1] : null;
            $getedValue             = null;
            $exceptionCaught        = false;
            $valuePrintable         = var_export($value, true);
            $expectedValuePrintable = var_export($expectedValue, true);
            $getedValuePrintable    = null;

            try
            {
                $getedValue = $field->validateValue($value);
                $getedValuePrintable = var_export($getedValue, true);
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
                    "Validated value not equals expected: field type - \"$fieldClassName\", value - \"$valuePrintable\", expected value - \"$expectedValuePrintable\", geted value \"$getedValuePrintable\""
                );
            }
            else
            {
                self::assertTrue
                (
                    $exceptionCaught,
                    "Expect \"$exceptionName\" on seting invalid value for validating: field type - \"$fieldClassName\", value - \"$valuePrintable\""
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
    public function convertingValueForPrint() : void
    {
        $field                  = $this->createFieldObject();
        $valuesForConverting    = static::getValuesForPrintConverting();
        $fieldClassName         = static::getFieldClassName();
        $exceptionName          = DomainException::class;

        if (count($valuesForConverting) <= 0)
        {
            self::markTestSkipped("no \"values for converting for print\" for testing \"$fieldClassName\" class");
        }

        foreach ($valuesForConverting as $values)
        {
            $value                  = $values[0];
            $hasExpectedValue       = array_key_exists(1, $values);
            $expectedValue          = $hasExpectedValue ? $values[1] : null;
            $getedValue             = null;
            $exceptionCaught        = false;
            $valuePrintable         = var_export($value, true);
            $expectedValuePrintable = var_export($expectedValue, true);
            $getedValuePrintable    = null;

            try
            {
                $getedValue = $field->convertValueForPrint($value);
                $getedValuePrintable = var_export($getedValue, true);
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
                    "Converting value for print not equals expected: field type - \"$fieldClassName\", value - \"$valuePrintable\", expected value - \"$expectedValuePrintable\", geted value \"$getedValuePrintable\""
                );
            }
            else
            {
                self::assertTrue
                (
                    $exceptionCaught,
                    "Expect \"$exceptionName\" on seting invalid value for converting for print: field type - \"$fieldClassName\", value - \"$valuePrintable\""
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
        $field                      = $this->createFieldObject();
        $fieldClassName             = static::getFieldClassName();
        $exceptionName              = DomainException::class;
        $randomValue                = $field->getRandomValue();
        $validatedValue             = null;
        $exceptionCaught            = false;
        $randomValuePrintable       = var_export($randomValue, true);
        $validatedValuePrintable    = null;

        try
        {
            $validatedValue = $field->validateValue($randomValue);
            $validatedValuePrintable = var_export($validatedValue, true);
        }
        catch (DomainException $exception)
        {
            $exceptionCaught = true;
        }

        self::assertEquals
        (
            $randomValue,
            $validatedValue,
            "Expect validation process gives no result on random value: field type - \"$fieldClassName\", random value - \"$randomValuePrintable\", validated random value value - \"$validatedValuePrintable\""
        );
        self::assertFalse
        (
            $exceptionCaught,
            "Expect \"$exceptionName\" exception on validating random value: field type - \"$fieldClassName\", random value - \"$randomValuePrintable\""
        );
    }
    /** **********************************************************************
     * get new participant field object
     *
     * @return  Field                       new field object
     ************************************************************************/
    private function createFieldObject() : Field
    {
        $fieldClassName = static::getFieldClassName();

        return new $fieldClassName;
    }
}