<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Data;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Procedures\Fields\Field as ProcedureField;
/** ***********************************************************************************************
 * Combined item data map
 * Display combined ONE data item
 * Collection of key => values, where key is procedure field and value some value
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class CombinedItem extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   ProcedureField $key         value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   ProcedureField $key         value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  ProcedureField[]            array of keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   ProcedureField $key         key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has value
     *
     * @param   mixed $value                value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach value to key
     *
     * @param   ProcedureField  $key        value key
     * @param   mixed           $value      value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof ProcedureField)
        {
            $needClass = ProcedureField::class;
            throw new InvalidArgumentException("key must be instance of \"$needClass\"");
        }

        parent::set($key, $value);
    }
}