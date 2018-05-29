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
class DBRow extends MapData
{
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
     * check map has value
     *
     * @param   mixed   $value              value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
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
        if (!is_string($key))
        {
            $keyType = gettype($key);
            throw new InvalidArgumentException("key must be string, \"$keyType\" caught");
        }
        if (strlen($key) <= 0)
        {
            throw new InvalidArgumentException('key must be not empty string');
        }
        if (!is_string($value) && !is_numeric($value) && !is_null($value))
        {
            $valueType = gettype($value);
            throw new InvalidArgumentException("value must be string, integer, float or null, \"$valueType\" caught");
        }

        parent::set($key, $value);
    }
}