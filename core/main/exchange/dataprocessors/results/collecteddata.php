<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Results;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Collected participants data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class CollectedData extends MapData
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
     * @return  ProvidedData                value
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
     * @param   ProvidedData $value         value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   Participant     $key        value key
     * @param   ProvidedData    $value      value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof Participant)
        {
            $needClass = Participant::class;
            throw new InvalidArgumentException("key must be instance of \"$needClass\"");
        }
        if (!$value instanceof ProvidedData)
        {
            $needClass = ProvidedData::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::set($key, $value);
    }
}