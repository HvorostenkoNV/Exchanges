<?php
declare(strict_types=1);

namespace Main\Data;

use InvalidArgumentException;
/** ***********************************************************************************************
 * Map data, collection of key => values
 *
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class MapData implements Map
{
    private
        $data           = [],
        $hashedValues   = [];
    /** **********************************************************************
     * construct
     *
     * @param   array   $data               data
     * @throws  InvalidArgumentException    incorrect key type
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
        {
            if (!is_string($key) && !is_int($key))
            {
                $keyType = gettype($key);
                throw new InvalidArgumentException("key must be string or integer, $keyType caught");
            }

            $hashedValue                        = $this->getVariableHash($value);
            $this->data[$key]                   = $value;
            $this->hashedValues[$hashedValue]   = null;
        }
    }
    /** **********************************************************************
     * delete value by key
     *
     * @param   mixed   $key                value key
     ************************************************************************/
    public function delete($key) : void
    {
        if (array_key_exists($key, $this->data))
        {
            $value          = $this->data[$key];
            $hashedValue    = $this->getVariableHash($value);

            unset($this->data[$key], $this->hashedValues[$hashedValue]);
        }
    }
    /** **********************************************************************
     * clear map
     ************************************************************************/
    public function clear() : void
    {
        $this->data         = [];
        $this->hashedValues = [];
    }
    /** **********************************************************************
     * get map count
     *
     * @return  int                         map count
     ************************************************************************/
    public function count() : int
    {
        return count($this->data);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   mixed   $key                value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return array_key_exists($key, $this->data)
            ? $this->data[$key]
            : null;
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  array                       array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return array_keys($this->data);
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   mixed   $key                key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return array_key_exists($key, $this->data);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   mixed   $value              value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return array_key_exists($this->getVariableHash($value), $this->hashedValues);
    }
    /** **********************************************************************
     * check map is empty
     *
     * @return  bool                        map is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return count($this->data) <= 0;
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   mixed   $key                key
     * @param   mixed   $value              value
     * @throws  InvalidArgumentException    incorrect key type
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!is_string($key) && !is_int($key))
        {
            $keyType = gettype($key);
            throw new InvalidArgumentException("key must be string or integer, $keyType caught");
        }

        $hashedValue                        = $this->getVariableHash($value);
        $this->data[$key]                   = $value;
        $this->hashedValues[$hashedValue]   = null;
    }
    /** **********************************************************************
     * get variable hash
     * using variable hash as unique value for variable keeping and identification
     *
     * @param   mixed   $value              value
     * @return  string                      variable hash
     ************************************************************************/
    private function getVariableHash($value) : string
    {
        switch (gettype($value))
        {
            case 'boolean':
                return 'boolean-'.json_encode($value);
                break;
            case 'array':
                return json_encode($value);
                break;
            case 'object':
                return spl_object_hash($value);
                break;
            case 'resource':
                return strval((int) $value);
                break;
            case 'NULL':
                return 'null-value';
                break;
            case 'string':
            case 'integer':
            case 'double':
            default:
                return strval($value);
        }
    }
}