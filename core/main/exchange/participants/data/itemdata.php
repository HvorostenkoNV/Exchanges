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
            if (!$value instanceof FieldValue)
            {
                $needClassName = FieldValue::class;
                throw new InvalidArgumentException("values must be instance of \"$needClassName\"");
            }
            if ($key !== $value->getField()->getParam('name'))
            {
                throw new InvalidArgumentException('keys must fields names');
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
        if (!$value instanceof FieldValue)
        {
            $needClassName = FieldValue::class;
            throw new InvalidArgumentException("values must be instance of \"$needClassName\"");
        }
        if ($key !== $value->getField()->getParam('name'))
        {
            throw new InvalidArgumentException('keys must fields names');
        }

        parent::set($key, $value);
    }
}