<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\FieldsSet;
/** ***********************************************************************************************
 * Data matching rules
 * Display dependency between some participants and some procedure fields
 * Tells how to find same participant field by other participant field
 * participant1 - participant1Field1 is same as participant2 - participant2Field3
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class DataMatchingRules extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   ParticipantsSet $key        value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   ParticipantsSet $key        value key
     * @return  FieldsSet                   value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  ParticipantsSet[]           array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   ParticipantsSet $key        key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   FieldsSet $value            value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   ParticipantsSet $key        value key
     * @param   FieldsSet       $value      value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof ParticipantsSet)
        {
            $needClass = ParticipantsSet::class;
            throw new InvalidArgumentException("key must be instance of \"$needClass\"");
        }
        if (!$value instanceof FieldsSet)
        {
            $needClass = FieldsSet::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::set($key, $value);
    }
}