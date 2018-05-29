<?php
declare(strict_types=1);

namespace Main\Data;
/** ***********************************************************************************************
 * Map data, collection of key => values
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
class MapData implements Map
{
    private
        $hashedKeys     = [],
        $hashedValues   = [],
        $relations      = [];
    /** **********************************************************************
     * clear map
     ************************************************************************/
    public function clear() : void
    {
        $this->hashedKeys   = [];
        $this->hashedValues = [];
        $this->relations    = [];
    }
    /** **********************************************************************
     * get map count
     *
     * @return  int                         map count
     ************************************************************************/
    public function count() : int
    {
        return count($this->relations);
    }
    /** **********************************************************************
     * delete value by key
     *
     * @param   mixed   $key                value key
     ************************************************************************/
    public function delete($key) : void
    {
        $hashedKey      = $this->getVariableHash($key);
        $hasKey         = array_key_exists($hashedKey, $this->relations);
        $hashedValue    = $hasKey ? $this->relations[$hashedKey] : null;

        if ($hasKey)
        {
            unset
            (
                $this->hashedKeys[$hashedKey],
                $this->hashedValues[$hashedValue],
                $this->relations[$hashedKey]
            );
        }
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   mixed   $key                value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        $hashedKey      = $this->getVariableHash($key);
        $hasKey         = array_key_exists($hashedKey, $this->relations);
        $hashedValue    = $hasKey ? $this->relations[$hashedKey] : null;

        return $hasKey
            ? $this->hashedValues[$hashedValue]
            : null;
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  array                       array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return array_values($this->hashedKeys);
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   mixed   $key                key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        $hashedKey = $this->getVariableHash($key);

        return array_key_exists($hashedKey, $this->relations);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   mixed   $value              value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        $hashedValue = $this->getVariableHash($value);

        return array_key_exists($hashedValue, $this->hashedValues);
    }
    /** **********************************************************************
     * check map is empty
     *
     * @return  bool                        map is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return count($this->relations) <= 0;
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   mixed   $key                key
     * @param   mixed   $value              value
     ************************************************************************/
    public function set($key, $value) : void
    {
        $hashedKey      = $this->getVariableHash($key);
        $hashedValue    = $this->getVariableHash($value);

        $this->hashedKeys[$hashedKey]       = $key;
        $this->hashedValues[$hashedValue]   = $value;
        $this->relations[$hashedKey]        = $hashedValue;
    }
    /** **********************************************************************
     * get variable hash
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
            case 'array':
                return json_encode($value);
            case 'object':
                return spl_object_hash($value);
            case 'resource':
                return strval((int) $value);
            case 'NULL':
                return 'null-value';
            case 'string':
            case 'integer':
            case 'double':
            default:
                return strval($value);
        }
    }
}