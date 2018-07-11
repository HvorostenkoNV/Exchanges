<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Participants\Fields\Field as ParticipantField;
/** ***********************************************************************************************
 * Data combining rules
 * Display participants fields weight
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class DataCombiningRules extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   ParticipantField $key       value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   ParticipantField $key       value key
     * @return  int                         value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  ParticipantField[]          array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   ParticipantField $key       key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   int $value                  value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   ParticipantField    $key    value key
     * @param   int                 $value  value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof ParticipantField)
        {
            $needClass = ParticipantField::class;
            throw new InvalidArgumentException("key must be instance of \"$needClass\"");
        }
        if (!is_int($value))
        {
            throw new InvalidArgumentException("value must be integer");
        }

        parent::set($key, $value);
    }
}