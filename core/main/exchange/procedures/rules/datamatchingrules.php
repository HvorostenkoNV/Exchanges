<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\FieldsSet as ProcedureFieldsSet;
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
     * @return  void
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   ParticipantsSet $key        value key
     * @return  ProcedureFieldsSet          value
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
     * @param   ProcedureFieldsSet $value   value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   ParticipantsSet     $key    value key
     * @param   ProcedureFieldsSet  $value  value
     * @return  void
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof ParticipantsSet)
        {
            $needClass  = ParticipantsSet::class;
            $getedType  = gettype($key);
            $getedValue = $getedType == 'object' ? get_class($key) : $getedType;

            throw new InvalidArgumentException("key must be instance of \"$needClass\", caught \"$getedValue\"");
        }
        if (!$value instanceof ProcedureFieldsSet)
        {
            $needClass  = ProcedureFieldsSet::class;
            $getedType  = gettype($value);
            $getedValue = $getedType == 'object' ? get_class($value) : $getedType;

            throw new InvalidArgumentException("value must be instance of \"$needClass\", caught \"$getedValue\"");
        }

        parent::set($key, $value);
    }
}