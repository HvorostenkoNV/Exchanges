<?php
declare(strict_types=1);

namespace Main\Data;

use InvalidArgumentException;
/** ***********************************************************************************************
 * Map data, collection of key => values
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class MapData implements Map
{
    private
        $data           = [],
        $includedValues = [];
    /** **********************************************************************
     * construct
     * @param   array   $data               data
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
        {
            $this->data[$key]                                       = $value;
            $this->includedValues[$this->getVariableHash($value)]   = NULL;
        }
    }
    /** **********************************************************************
     * delete value by index
     * @param   mixed   $key                value index
     ************************************************************************/
    public function delete($key) : void
    {
        if (!array_key_exists($key, $this->data))
            return;

        $value = $this->data[$key];
        unset($this->data[$key]);
        unset($this->includedValues[$this->getVariableHash($value)]);
    }
    /** **********************************************************************
     * clear data
     ************************************************************************/
    public function clear() : void
    {
        $this->data             = [];
        $this->includedValues   = [];
    }
    /** **********************************************************************
     * get data count
     ************************************************************************/
    public function count() : int
    {
        return count($this->data);
    }
    /** **********************************************************************
     * get value by index
     * @param   mixed   $key                value index
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return array_key_exists($key, $this->data)
            ? $this->data[$key]
            : NULL;
    }
    /** **********************************************************************
     * get value by index
     * @return  array                       keys queue
     ************************************************************************/
    public function getKeys() : array
    {
        return array_keys($this->data);
    }
    /** **********************************************************************
     * check map has value
     * @param   mixed   $value              value
     * @return  bool                        has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return array_key_exists
        (
            $this->getVariableHash($value),
            $this->includedValues
        );
    }
    /** **********************************************************************
     * check data is empty
     * @return  bool                        collection is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return count($this->data) <= 0;
    }
    /** **********************************************************************
     * attach value to index
     * @param   mixed   $key                value index
     * @param   mixed   $value              value
     * @throws  InvalidArgumentException    incorrect key type
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!is_string($key) && !is_int($key))
            throw new InvalidArgumentException('Incorrect key type');

        $this->data[$key]                                       = $value;
        $this->includedValues[$this->getVariableHash($value)]   = NULL;
    }
    /** **********************************************************************
     * get variable hash
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
                return strval(intval($value));
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