<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    DomainException,
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\ClassTesting\Exchange\Participants\FieldsTypes\ParticipantFieldClass as FieldTypeTest,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Data\FieldValue;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\FieldValue classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldValueTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check creating object
     *
     * @test
     * @throws
     ************************************************************************/
    public function creatingObject() : void
    {
        foreach (FieldsTypesManager::getAvailableFieldsTypes() as $type)
        {
            $participantFieldParams = new MapData(['name' => 'test', 'type' => $type]);
            $participantField       = new Field($participantFieldParams);
            $fieldTypeUnitTest      = $this->getFieldTypeUnitTest($type);
            $valuesForValidation    = $fieldTypeUnitTest::getValuesForValidation();

            foreach ($valuesForValidation as $values)
            {
                $value              = $values[0];
                $expectException    = count($values) === 1;

                if ($expectException)
                {
                    try
                    {
                        new FieldValue($value, $participantField);
                        $exceptionName = DomainException::class;
                        self::fail("Expect \"$exceptionName\" on creating new object with incorrect params");
                    }
                    catch (DomainException $exception)
                    {
                        self::assertTrue(true);
                    }
                }
                else
                {
                    try
                    {
                        new FieldValue($value, $participantField);
                        self::assertTrue(true);
                    }
                    catch (DomainException $exception)
                    {
                        $exceptionName = DomainException::class;
                        self::fail("Unexpected \"$exceptionName\" on creating new object with correct params");
                    }
                }
            }
        }
    }
    /** **********************************************************************
     * check read operations
     *
     * @test
     * @depends creatingObject
     * @throws
     ************************************************************************/
    public function readOperations() : void
    {
        foreach (FieldsTypesManager::getAvailableFieldsTypes() as $type)
        {
            $participantFieldParams = new MapData(['name' => 'test', 'type' => $type]);
            $participantField       = new Field($participantFieldParams);
            $fieldType              = FieldsTypesManager::getField($type);
            $fieldTypeClassName     = get_class($fieldType);
            $fieldTypeUnitTest      = $this->getFieldTypeUnitTest($type);
            $valuesForValidation    = $fieldTypeUnitTest::getValuesForValidation();

            foreach ($valuesForValidation as $values)
            {
                if (count($values) > 1)
                {
                    $value      = $values[0];
                    $fieldValue = new FieldValue($value, $participantField);

                    self::assertEquals
                    (
                        $fieldType->validateValue($value),
                        $fieldValue->getValue(),
                        "Expect get same validation result as in \"$fieldTypeClassName\" class"
                    );
                    self::assertEquals
                    (
                        $fieldType->convertValueForPrint($fieldValue->getValue()),
                        $fieldValue->getPrintableValue(),
                        "Expect get same converting for print result as in \"$fieldTypeClassName\" class"
                    );
                    self::assertEquals
                    (
                        $participantField,
                        $fieldValue->getField(),
                        "Expect get same participant field object as was seted"
                    );
                }
            }
        }
    }
    /** **********************************************************************
     * get field type unit test
     *
     * @param   string  $type               field
     * @return  FieldTypeTest               unit test
     * @throws
     ************************************************************************/
    private function getFieldTypeUnitTest(string $type) : FieldTypeTest
    {
        $fieldType          = FieldsTypesManager::getField($type);
        $unitTestNamespace  = (new ReflectionClass(FieldTypeTest::class))->getNamespaceName();
        $fieldTypeClassName = (new ReflectionClass($fieldType))->getShortName();
        $unitTestClassName  = $unitTestNamespace.'\\'.$fieldTypeClassName.'Test';

        return new $unitTestClassName;
    }
}