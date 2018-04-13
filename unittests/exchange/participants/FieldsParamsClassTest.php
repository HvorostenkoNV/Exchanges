<?php
declare(strict_types=1);

use Main\Exchange\Participants\Data\FieldsParams;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\FieldsParams class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsParamsClassTest extends ExchangeTestCase
{
    private static
        $correctWorkFields      = [],
        $incorrectWorkFields    = [],
        $availableFieldsTypes   = [];
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        self::$correctWorkFields    = ['field1', 'field2', 'field3', 'field4'];
        self::$incorrectWorkFields  =
        [
            '',
            1,
            1.5,
            true,
            [1, 2, 3],
            new FieldsParams,
            NULL
        ];
        self::$availableFieldsTypes =
        [
            'increment',
            'integer', 'float', 'string', 'boolean',
            'array-integer', 'array-float', 'array-string', 'array-boolean'
        ];
    }
    /** **********************************************************************
     * check empty object
     * @test
     ************************************************************************/
    public function emptyObject() : void
    {
        $fieldsParams = new FieldsParams;

        self::assertTrue
        (
            $fieldsParams->isEmpty(),
            'New FieldsParams object is not empty'
        );
        self::assertEquals
        (
            0, $fieldsParams->count(),
            'New FieldsParams object values count is not zero'
        );
    }
    /** **********************************************************************
     * test work fields methods
     * @test
     ************************************************************************/
    public function workFields() : void
    {
        $fieldsParams       = new FieldsParams;
        $correctValues      = self::$correctWorkFields;
        $incorrectValues    = self::$incorrectWorkFields;
        $randomCorrectValue = $correctValues[array_rand($correctValues)];

        $fieldsParams->setFields($correctValues);
        self::assertEquals
        (
            $correctValues, $fieldsParams->getFields(),
            'Geted work fields not equal saved'
        );

        foreach ($incorrectValues as $incorrectValue)
        {
            try
            {
                $fieldsParams->setFields(array_merge($correctValues, [$incorrectValue]));
                self::fail('Expect '.InvalidArgumentException::class.' exception on seting non string work fields');
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }

        try
        {
            $fieldsParams->setFields(array_merge($correctValues, [$randomCorrectValue]));
            self::fail('Expect '.InvalidArgumentException::class.' exception on seting duplicates work fields');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        self::assertEquals
        (
            $correctValues, $fieldsParams->getFields(),
            'Geted work fields not equal saved before tries to set incorrect values'
        );
    }
    /** **********************************************************************
     * test required fields methods
     * @test
     * @depends workFields
     ************************************************************************/
    public function requiredFields() : void
    {
        $fieldsParams   = new FieldsParams;
        $fields         = self::$correctWorkFields;
        $randomField    = $fields[array_rand($fields)];
        $incorrectField = 'testField';

        while (in_array($incorrectField, $fields))
            $incorrectField .= '1';

        $fieldsParams->setFields($fields);

        $fieldsParams->setFieldRequired($randomField, true);
        self::assertTrue
        (
            $fieldsParams->getFieldRequired($randomField),
            'Marked field must be required'
        );

        $fieldsParams->setFieldRequired($randomField, false);
        self::assertFalse
        (
            $fieldsParams->getFieldRequired($randomField),
            'Marked field must be not required'
        );

        try
        {
            $fieldsParams->setFieldRequired($incorrectField, true);
            self::fail('Expect '.InvalidArgumentException::class.' exception on seting field required with incorrect field name');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        try
        {
            $fieldsParams->getFieldRequired($incorrectField);
            self::fail('Expect '.InvalidArgumentException::class.' exception on call "getFieldRequired" with undefined field');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * test fields types
     * @test
     * @depends workFields
     ************************************************************************/
    public function fieldsTypes() : void
    {
        $fieldsParams           = new FieldsParams;
        $availableFieldsTypes   = self::$availableFieldsTypes;
        $fields                 = [];
        $incorrectField         = 'testField';
        $incorrectFieldType     = 'testFieldType';
        $fieldWithDefaultType   = 'fieldWithDefaultType';
        $defaultFieldType       = 'string';
        $randomField            = '';

        foreach ($availableFieldsTypes as $fieldType)
            $fields[$fieldType.'Field'] = $fieldType;
        while (array_key_exists($incorrectField, $fields))
            $incorrectField .= '1';
        while (in_array($incorrectFieldType, $fields))
            $incorrectFieldType .= '1';
        if (count($fields) > 0)
            $randomField = array_rand($fields);
        while (array_key_exists($fieldWithDefaultType, $fields))
            $fieldWithDefaultType .= '1';

        self::assertEquals
        (
            $availableFieldsTypes, FieldsParams::getAvailableFieldsTypes(),
            'Array of available fields types not as expected'
        );

        $fieldsParams->setFields(array_keys($fields));
        foreach ($fields as $field => $type)
        {
            $fieldsParams->setFieldType($field, $type);
            self::assertEquals
            (
                $type, $fieldsParams->getFieldType($field),
                'Geted field type not equals seted.'
            );
        }

        try
        {
            $fieldsParams->setFieldType($randomField, $incorrectFieldType);
            self::fail('Expect '.InvalidArgumentException::class.' exception on seting incorrect field type');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        try
        {
            $fieldsParams->getFieldType($incorrectField);
            self::fail('Expect '.InvalidArgumentException::class.' exception on call "getFieldType" with undefined field');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        $fieldsParams->setFields([$fieldWithDefaultType]);
        self::assertEquals
        (
            $defaultFieldType, $fieldsParams->getFieldType($fieldWithDefaultType),
            'Expect get default field type on field with non seted field type'
        );

        $fieldsParams->setFields(array_keys($fields));
        foreach ($fields as $field => $type)
            self::assertEquals
            (
                $defaultFieldType, $fieldsParams->getFieldType($field),
                'Expect get default field type after refresh work fields array with same value'
            );
    }
    /** **********************************************************************
     * check clearing operations
     * @test
     * @depends workFields
     ************************************************************************/
    public function clearingOperations() : void
    {
        $fieldsParams = new FieldsParams;

        $fieldsParams->setFields(self::$correctWorkFields);
        $fieldsParams->clear();

        self::assertTrue
        (
            $fieldsParams->isEmpty(),
            'FieldsParams object is not empty after clearing'
        );
        self::assertEquals
        (
            0, $fieldsParams->count(),
            'FieldsParams object values count is not zero after clearing'
        );
    }
}