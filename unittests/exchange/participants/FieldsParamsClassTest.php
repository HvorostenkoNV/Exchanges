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
	 * @return  FieldsParams                    params object filled with available fields
	 ************************************************************************/
	public function availableFields() : FieldsParams
	{
		$fieldsParams       = new FieldsParams;
		$correctValues      = ['field1', 'field2', 'field3', 'field4'];
		$incorrectValues    = [1, 1.5, true, [1, 2, 3], new FieldsParams];
		$correctValueRand   = $correctValues[array_rand($correctValues)];

		$fieldsParams->setAvailableFields(array_merge($correctValues, $incorrectValues));
		self::assertEquals
		(
			$correctValues, $fieldsParams->getAvailableFields(),
			'Saved available fields incorrect, empty or contains non string'
		);

		$fieldsParams->setAvailableFields(array_merge($correctValues, [$correctValueRand]));
		self::assertEquals
		(
			$correctValues, $fieldsParams->getAvailableFields(),
			'Saved available fields contains duplicates'
		);

		return $fieldsParams;
	}
	/** **********************************************************************
	 * test required fields methods
	 * @test
	 * @depends availableFields
	 * @param   FieldsParams    $fieldsParams       params object filled with available fields
	 ************************************************************************/
	public function requiredFields(FieldsParams $fieldsParams) : void
	{
		$availableFields            = $fieldsParams->getAvailableFields();
		$requiredFields             = [];
		$incorrectRequiredFields    = ['test1', 'test2'];
		$randRequiredField          = '';

		for( $index = 0; $index <= count($availableFields) - 2; $index++ )
		{
			$requiredFields[] = $availableFields[$index];
			if( strlen($randRequiredField) <= 0 )
				$randRequiredField = $availableFields[$index];
		}

		$fieldsParams->setRequiredFields(array_merge($requiredFields, $incorrectRequiredFields));
		self::assertEquals
		(
			$requiredFields, $fieldsParams->getRequiredFields(),
			'Saved required fields incorrect, empty or contains non available fields'
		);

		$fieldsParams->setRequiredFields(array_merge($requiredFields, [$randRequiredField]));
		self::assertEquals
		(
			$requiredFields, $fieldsParams->getRequiredFields(),
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
			'integer', 'float', 'string', 'boolean',
			'array-integer', 'array-float', 'array-string', 'array-boolean'
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
			$expectedFieldsTypes, $availableFieldsTypes,
			'Array of available fields types not as expected'
		);

		$fieldsParams->setAvailableFields(array_keys($fields));
		foreach( $fields as $field => $type )
		{
			$fieldsParams->setFieldType($field, $type);
			self::assertEquals
			(
				$type, $fieldsParams->getFieldType($field),
				'Geted field type not equals seted.'
			);
		}

		$fieldsParams->setFieldType($randCorrectField, $incorrectFieldType);
		self::assertEquals
		(
			$defaultFieldType, $fieldsParams->getFieldType($randCorrectField),
			'Expect default field type as '.$defaultFieldType
		);

		$fieldsParams->setFieldType($incorrectField, $defaultFieldType);
		self::assertEquals
		(
			NULL, $fieldsParams->getFieldType($incorrectField),
			'Expect NULL on call to undefined field'
		);
	}
}