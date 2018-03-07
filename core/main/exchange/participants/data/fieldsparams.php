<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;
/** ***********************************************************************************************
 * Participants fields params abstract rules
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldsParams implements Data
{
	private
		$availableFields    = [],
		$requiredFields     = [],
		$fieldsTypes        = [];
	private static
		$availableFieldsTypes   =
		[
			'increment',
			'integer', 'string', 'boolean',
			'array-integer', 'array-string', 'array-boolean'
		],
		$defaultFieldType       = 'string';
	/** **********************************************************************
	 * set available fields
	 * @return  array                   available fields types
	 ************************************************************************/
	public static function getAvailableFieldsTypes() : array
	{
		return self::$availableFieldsTypes;
	}
	/** **********************************************************************
	 * set available fields
	 * @param   string[]    $fields     fields
	 ************************************************************************/
	public function setAvailableFields(array $fields) : void
	{
		$availableFields       = array_filter($fields, function($value)
		{
			return is_string($value);
		});
		$availableFields       = array_unique($availableFields);
		$this->availableFields = $availableFields;
	}
	/** **********************************************************************
	 * get available fields
	 * @return  string[]                available fields
	 ************************************************************************/
	public function getAvailableFields() : array
	{
		return $this->availableFields;
	}
	/** **********************************************************************
	 * set required fields
	 * @param   string[]    $fields     fields
	 ************************************************************************/
	public function setRequiredFields(array $fields) : void
	{
		$requiredFields         = array_filter($fields, function($value)
		{
			return is_string($value);
		});
		$requiredFields         = array_unique($requiredFields);
		$this->requiredFields   = [];

		foreach( $requiredFields as $field )
			if( in_array($field, $this->availableFields) )
				$this->requiredFields[] = $field;
	}
	/** **********************************************************************
	 * get required fields
	 * @return  string[]                required fields
	 ************************************************************************/
	public function getRequiredFields()
	{
		return $this->requiredFields;
	}
	/** **********************************************************************
	 * set field type
	 * @param   string  $field          field
	 * @param   string  $type           field type
	 ************************************************************************/
	public function setFieldType(string $field, string $type) : void
	{
		if( !in_array($field, $this->availableFields) ) return;

		unset($this->fieldsTypes[$field]);
		if( in_array($type, self::$availableFieldsTypes) )
			$this->fieldsTypes[$field] = $type;
	}
	/** **********************************************************************
	 * get field type
	 * @param   string  $field          field
	 * @return  string|NULL             field type or NULL if field undefined
	 ************************************************************************/
	public function getFieldType(string $field) : ?string
	{
		if( !in_array($field, $this->availableFields) )
			return NULL;
		return array_key_exists($field, $this->fieldsTypes)
			? $this->fieldsTypes[$field]
			: self::$defaultFieldType;
	}
}