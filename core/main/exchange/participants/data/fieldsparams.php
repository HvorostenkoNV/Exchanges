<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use InvalidArgumentException;
/** ***********************************************************************************************
 * Participants fields params abstract rules
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldsParams implements Data
{
    private
        $fieldsInfo = [];
    private static
        $availableFieldsTypes   =
        [
            'increment',
            'integer', 'float', 'string', 'boolean',
            'array-integer', 'array-float', 'array-string', 'array-boolean'
        ],
        $defaultFieldType       = 'string';
    /** **********************************************************************
     * set available fields
     * @return  string[]                        available fields types
     ************************************************************************/
    public static function getAvailableFieldsTypes() : array
    {
        return self::$availableFieldsTypes;
    }
    /** **********************************************************************
     * clear data
     ************************************************************************/
    public function clear() : void
    {
        $this->fieldsInfo = [];
    }
    /** **********************************************************************
     * get data count
     * @return  int                             items count
     ************************************************************************/
    public function count() : int
    {
        return count($this->fieldsInfo);
    }
    /** **********************************************************************
     * check data is empty
     * @return  bool                            collection is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return count($this->fieldsInfo) <= 0;
    }
    /** **********************************************************************
     * set work fields
     * @param   string[]    $fields             fields
     * @throws  InvalidArgumentException        fields contains non strings or duplicates
     ************************************************************************/
    public function setFields(array $fields) : void
    {
        $workFields = array_filter($fields, function($value)
        {
            return is_string($value) && strlen($value) > 0;
        });
        if (count($workFields) != count($fields))
            throw new InvalidArgumentException('Seted fields contains non strings');

        $workFields = array_unique($workFields);
        if (count($workFields) != count($fields))
            throw new InvalidArgumentException('Seted fields contains non duplicates');

        $this->fieldsInfo = [];
        foreach ($workFields as $field)
            $this->fieldsInfo[$field] =
            [
                'required'  => false,
                'type'      => self::$defaultFieldType
            ];
    }
    /** **********************************************************************
     * get work fields
     * @return  string[]                        available fields
     ************************************************************************/
    public function getFields() : array
    {
        return array_keys($this->fieldsInfo);
    }
    /** **********************************************************************
     * mark field required
     * @param   string  $field                  field name
     * @param   bool    $value                  required
     * @throws  InvalidArgumentException        undefined field
     ************************************************************************/
    public function setFieldRequired(string $field, bool $value) : void
    {
        if (!array_key_exists($field, $this->fieldsInfo))
            throw new InvalidArgumentException("Field $field is not in work fields");

        $this->fieldsInfo[$field]['required'] = $value;
    }
    /** **********************************************************************
     * get field required value
     * @param   string  $field                  field name
     * @return  bool                            field is required
     * @throws  InvalidArgumentException        undefined field
     ************************************************************************/
    public function getFieldRequired(string $field) : bool
    {
        if (!array_key_exists($field, $this->fieldsInfo))
            throw new InvalidArgumentException("Field $field is not in work fields");

        return $this->fieldsInfo[$field]['required'];
    }
    /** **********************************************************************
     * set field type
     * @param   string  $field                  field
     * @param   string  $type                   field type
     * @throws  InvalidArgumentException        undefined field or incorrect field type
     ************************************************************************/
    public function setFieldType(string $field, string $type) : void
    {
        if (!array_key_exists($field, $this->fieldsInfo))
            throw new InvalidArgumentException("Field \"$field\" is not in work fields");
        if (!in_array($type, self::$availableFieldsTypes))
            throw new InvalidArgumentException("Field type \"$type\" is not available field type");

        $this->fieldsInfo[$field]['type'] = $type;
    }
    /** **********************************************************************
     * get field type
     * @param   string  $field                  field
     * @return  string                          field type
     * @throws  InvalidArgumentException        undefined field
     ************************************************************************/
    public function getFieldType(string $field) : string
    {
        if (!array_key_exists($field, $this->fieldsInfo))
            throw new InvalidArgumentException("Field \"$field\" is not in work fields");

        return $this->fieldsInfo[$field]['type'];
    }
}