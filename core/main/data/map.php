<?php
declare(strict_types=1);

namespace Main\Data;
/** ***********************************************************************************************
 * Map data interface, collection of key => values
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
interface Map extends Data
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   mixed   $key                value key
     ************************************************************************/
    public function delete($key) : void;
    /** **********************************************************************
     * get value by key
     *
     * @param   mixed   $key                value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key);
    /** **********************************************************************
     * get map keys
     *
     * @return  array                       array of keys
     ************************************************************************/
    public function getKeys() : array;
    /** **********************************************************************
     * check map has key
     *
     * @param   mixed   $key                key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool;
    /** **********************************************************************
     * check map has value
     *
     * @param   mixed   $value              value
     * @return  bool                        map has value
     ************************************************************************/
    public function hasValue($value) : bool;
    /** **********************************************************************
     * attach value to key
     *
     * @param   mixed   $key                key
     * @param   mixed   $value              value
     ************************************************************************/
    public function set($key, $value) : void;
}