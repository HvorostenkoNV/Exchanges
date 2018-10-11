<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Data;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Matched item data map
 * Display different participants data as ONE item
 * Collection of key => values, where key is participant and value is its data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchedItem extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   Participant $key            value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   Participant $key            value key
     * @return  ItemData                    value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  Participant[]               array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   Participant $key            key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   ItemData $value             value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   Participant $key            value key
     * @param   ItemData    $value          value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof Participant)
        {
            $needClass  = Participant::class;
            $getedType  = gettype($key);
            $getedValue = $getedType == 'object' ? get_class($key) : $getedType;

            throw new InvalidArgumentException("key must be instance of \"$needClass\", caught \"$getedValue\"");
        }
        if (!$value instanceof ItemData)
        {
            $needClass  = ItemData::class;
            $getedType  = gettype($value);
            $getedValue = $getedType == 'object' ? get_class($value) : $getedType;

            throw new InvalidArgumentException("value must be instance of \"$needClass\", caught \"$getedValue\"");
        }

        parent::set($key, $value);
    }
}