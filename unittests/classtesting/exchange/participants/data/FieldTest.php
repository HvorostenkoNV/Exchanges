<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Data;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Participants\Data\Field;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\Field class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check read/write operations
     *
     * @test
     ************************************************************************/
    public function readWriteOperations() : void
    {
        $field          = new Field;
        $fieldName      = ['field1', 'field2', 'field3'];
        $fieldRequired  = [true, false, true];
        $fieldType      = [];
        $availableTypes = Field::getAvailableTypes();

        foreach (array_rand($availableTypes, 3) as $index)
        {
            $fieldType[] = $availableTypes[$index];
        }

        foreach ($fieldName as $name)
        {
            $field->setName($name);
            self::assertEquals
            (
                $name,
                $field->getName(),
                'Field name is not equal seted'
            );
        }
        foreach ($fieldRequired as $value)
        {
            $field->setRequired($value);
            self::assertEquals
            (
                $value,
                $field->isRequired(),
                'Field required value is not equal seted'
            );
        }
        foreach ($fieldType as $type)
        {
            $field->setType($type);
            self::assertEquals
            (
                $type,
                $field->getType(),
                'Field type is not equal seted'
            );
        }
    }
    /** **********************************************************************
     * check incorrect read/write operations
     *
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function incorrectReadWriteOperations() : void
    {
        $field          = new Field;
        $defaultType    = Field::getDefaultType();
        $availableTypes = Field::getAvailableTypes();
        $incorrectType  = 'incorrectType';

        while (in_array($incorrectType, $availableTypes))
        {
            $incorrectType .= '1';
        }

        $field->setType($incorrectType);
        self::assertEquals
        (
            $defaultType,
            $field->getType(),
            'Expect default field type after seting incorrect type'
        );
    }
    /** **********************************************************************
     * check empty object behavior
     *
     * @test
     ************************************************************************/
    public function emptyObject() : void
    {
        $field = new Field;

        self::assertEquals
        (
            '',
            $field->getName(),
            'Expect empty string as field name in empty field object'
        );
        self::assertFalse
        (
            $field->isRequired(),
            'Expect false value as field required value in empty field object'
        );
        self::assertNotEquals
        (
            '',
            Field::getDefaultType(),
            'Expect default field type is not empty'
        );
        self::assertEquals
        (
            Field::getDefaultType(),
            $field->getType(),
            'Expect default field type as field type in empty field object'
        );
    }
}