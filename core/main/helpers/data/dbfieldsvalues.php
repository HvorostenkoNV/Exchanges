<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
    InvalidArgumentException,
    Main\Data\MapData;
/** ***********************************************************************************************
 * DB row item map data, collection of key => values
 * Based on db query row
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class DBFieldsValues extends MapData
{
    /** **********************************************************************
     * construct
     *
     * @param   array   $data               data
     * @throws  InvalidArgumentException    incorrect data array argument
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
        {
            if (!is_string($key) || strlen($key) <= 0)
            {
                throw new InvalidArgumentException('incorrect array data: data keys must be string');
            }
            if (!$this->checkValueValid($value))
            {
                throw new InvalidArgumentException('incorrect array data: data values must be string, integer, float or null');
            }
        }

        parent::__construct($data);
    }
    /** **********************************************************************
     * delete value by key
     *
     * @param   string  $key                value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   string  $key                value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  string[]                    array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   string  $key                key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   string  $key                value key
     * @param   mixed   $value              value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!is_string($key) || strlen($key) <= 0)
        {
            throw new InvalidArgumentException('key must be string');
        }
        if (!$this->checkValueValid($value))
        {
            throw new InvalidArgumentException('value must be string, integer, float or null');
        }

        parent::set($key, $value);
    }
    /** **********************************************************************
     * check value valid
     *
     * @param   mixed   $value              value
     * @return  bool                        value valid
     ************************************************************************/
    private function checkValueValid($value)
    {
        switch (gettype($value))
        {
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                return true;
                break;
            case 'boolean':
            case 'array':
            case 'object':
            case 'resource':
            default:
                return false;
        }
    }
}