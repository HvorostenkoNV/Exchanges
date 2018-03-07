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
	/** **********************************************************************
	 * test available fields methods
	 * @test
	 ************************************************************************/
	public function availableFields() : void
	{
		$fieldsParams       = new FieldsParams;
		$values             = ['field1', 'field2', 'field3', 'field4'];
		$incorrectValues    = [1, new FieldsParams, [1, 2, 3]];

		$fieldsParams->setAvailableFields(array_merge($values, $incorrectValues));
		self::assertEquals
		(
			$fieldsParams->getAvailableFields(), $values,
			'Saved available fields incorrect, empty or contains non string'
		);

		$fieldsParams->setAvailableFields(array_merge($values, [$values[count($values) - 1]]));
		self::assertEquals
		(
			$fieldsParams->getAvailableFields(), $values,
			'Saved available fields contains duplicates'
		);
	}
	/** **********************************************************************
	 * test required fields methods
	 * @test
	 * @depends availableFields
	 ************************************************************************/
	public function requiredFields() : void
	{
		$fieldsParams               = new FieldsParams;
		$availableFields            = ['field1', 'field2', 'field3', 'field4'];
		$requiredFields             = [];
		$incorrectRequiredFields    = ['test1', 'test2'];

		foreach( array_rand($availableFields, count($availableFields) - 2) as $index )
			$requiredFields[] = $availableFields[$index];

		$fieldsParams->setAvailableFields($availableFields);

		$fieldsParams->setRequiredFields(array_merge($requiredFields, $incorrectRequiredFields));
		self::assertEquals
		(
			$fieldsParams->getRequiredFields(), $requiredFields,
			'Saved required fields incorrect, empty or contains non available fields'
		);

		$fieldsParams->setRequiredFields(array_merge($requiredFields, [$requiredFields[count($requiredFields) - 1]]));
		self::assertEquals
		(
			$fieldsParams->getRequiredFields(), $requiredFields,
			'Saved required fields contains duplicates'
		);
	}
	/** **********************************************************************
	 * test fields types
	 * @test
	 * @depends availableFields
	 ************************************************************************/
	public function fieldsTypes() : void
	{
		$fieldsParams           = new FieldsParams;
		$availableFieldsTypes   = FieldsParams::getAvailableFieldsTypes();
		$expectedFieldsTypes    =
		[
			'increment',
			'integer', 'string', 'boolean',
			'array-integer', 'array-string', 'array-boolean'
		];
		$fields                 =
		[
			'incrementField'        => 'increment',
			'integerField'          => 'integer',
			'stringField'           => 'string',
			'booleanField'          => 'boolean',
			'arrayOfIntegersField'  => 'array-integer',
			'arrayOfStringsField'   => 'array-string',
			'arrayOfBooleansField'  => 'array-boolean'
		];
		$defaultFieldType       = 'string';
		$incorrectField         = 'testField';
		$incorrectFieldType     = 'test';
		$randCorrectField       = array_rand($fields);

		self::assertEquals
		(
			$availableFieldsTypes, $expectedFieldsTypes,
			'Array of available fields types not as expected'
		);

		$fieldsParams->setAvailableFields(array_keys($fields));
		foreach( $fields as $field => $type )
		{
			$fieldsParams->setFieldType($field, $type);
			self::assertEquals
			(
				$fieldsParams->getFieldType($field), $type,
				'Geted field type not equals seted.'
			);
		}

		$fieldsParams->setFieldType($randCorrectField, $incorrectFieldType);
		var_dump($fieldsParams->getFieldType($randCorrectField));
		self::assertEquals
		(
			$fieldsParams->getFieldType($randCorrectField), $defaultFieldType,
			'Expect default field type as '.$defaultFieldType
		);

		$fieldsParams->setFieldType($incorrectField, $defaultFieldType);
		self::assertEquals
		(
			$fieldsParams->getFieldType($incorrectField), NULL,
			'Expect NULL on call to undefined field'
		);
	}
}