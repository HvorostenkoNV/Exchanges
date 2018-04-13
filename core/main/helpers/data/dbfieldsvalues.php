<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
    InvalidArgumentException,
    Main\Data\MapData;
/** ***********************************************************************************************
 * Item map data, based on db query row, collection of key => values
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class DBFieldsValues extends MapData
{
    /** **********************************************************************
     * construct
     * @param   array   $data               data
     * @throws  InvalidArgumentException    incorrect array
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
        {
            if (!is_string($key) || strlen($key) <= 0)
                throw new InvalidArgumentException('Incorrect array data. Data keys must be string.');
            if (!$this->checkValueValid($value))
                throw new InvalidArgumentException('Incorrect array data. Data values must be string, integer or float. '.gettype($value).' cached');
        }

        parent::__construct($data);
    }
    /** **********************************************************************
     * delete value by index
     * @param   string  $key                value index
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by index
     * @param   string  $key                value index
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get value by index
     * @return  string[]                    keys queue
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * attach value to index
     * @param   string  $key                value index
     * @param   mixed   $value              value
     * @throws  InvalidArgumentException    incorrect key type
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!is_string($key) || strlen($key) <= 0)
            throw new InvalidArgumentException('Key must be string.');
        if (!$this->checkValueValid($value))
            throw new InvalidArgumentException('Value must be string, integer or float. '.gettype($value).' cached');

        parent::set($key, $value);
    }
    /** **********************************************************************
     * check value valid
     * @param   mixed   $value              value
     * @return  bool                        value valid
     ************************************************************************/
    private function checkValueValid($value)
    {
        switch (gettype($value))
        {
            case 'integer':
            case 'double':
            case 'string':
                return true;
                break;
            case 'boolean':
            case 'array':
            case 'object':
            case 'resource':
            case 'NULL':
            default:
                return false;
        }
    }
}