<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    InvalidArgumentException,
    Main\Data\MapData;
/** ***********************************************************************************************
 * Participants item map data, item fields data.
 * Map data, collection of key => values
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class ItemData extends MapData
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
                throw new InvalidArgumentException('incorrect array data: data values must be scalar or array of scalar values with same type');
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
     * @return  string[]                    map keys
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
            throw new InvalidArgumentException('value must be scalar or array of scalar values with same type');
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
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return true;
                break;
            case 'array':
                if (count($value) == 0)
                {
                    return true;
                }

                $valuesType = gettype(array_values($value)[0]);

                if (!in_array($valuesType, ['boolean', 'integer', 'double', 'string']))
                {
                    return false;
                }

                foreach ($value as $arrayValue)
                {
                    if (gettype($arrayValue) != $valuesType)
                    {
                        return false;
                    }
                }

                return true;
                break;
            case 'object':
            case 'resource':
            case 'null':
            default:
                return false;
        }
    }
}