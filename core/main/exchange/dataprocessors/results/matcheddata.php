<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Results;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\DataProcessors\Data\MatchedItem;
/** ***********************************************************************************************
 * Participants matched data
 * Collection of MatchedItem objects
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchedData extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   int $key                    value key
     * @return  void
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   int $key                    value key
     * @return  MatchedItem                 value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  int[]                       array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   int $key                    key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   MatchedItem $value          value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   int         $key            value key
     * @param   MatchedItem $value          value
     * @return  void
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!is_int($key) || $key <= 0)
        {
            $getedType  = gettype($key);
            $getedValue = $getedType == 'integer' ? 0 : $getedType;

            throw new InvalidArgumentException("key must be integer and not zero, caught \"$getedValue\"");
        }
        if (!$value instanceof MatchedItem)
        {
            $needClass  = MatchedItem::class;
            $getedType  = gettype($value);
            $getedValue = $getedType == 'object' ? get_class($value) : $getedType;

            throw new InvalidArgumentException("value must be instance of \"$needClass\", caught \"$getedValue\"");
        }

        parent::set($key, $value);
    }
}