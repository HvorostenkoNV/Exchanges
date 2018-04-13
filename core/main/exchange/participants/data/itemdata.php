<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    InvalidArgumentException,
    Main\Data\MapData;
/** ***********************************************************************************************
 * Participants item map data, item fields data. Collection of key => values
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class ItemData extends MapData
{
    /** **********************************************************************
     * construct
     * @param   array   $data               data
     * @throws  InvalidArgumentException    incorrect array
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
            if (!is_string($key) || strlen($key) <= 0 || !$this->checkValueValid($value))
                throw new InvalidArgumentException('Incorrect array data. Data keys must be string. Data values must be scalar or array of scalar values with same type');

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
        if (!is_string($key) || strlen($key) <= 0 || !$this->checkValueValid($value))
            throw new InvalidArgumentException('Incorrect key or value. Key must be string. Value must be scalar or array of scalar values with same type');

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
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return true;
                break;
            case 'array':
                if (count($value) == 0)
                    return true;

                $valuesType = gettype(array_values($value)[0]);

                if (!in_array($valuesType, ['boolean', 'integer', 'double', 'string']))
                    return false;

                foreach ($value as $arrayValue)
                    if (gettype($arrayValue) != $valuesType)
                        return false;

                return true;
                break;
            case 'object':
            case 'resource':
            case 'NULL':
            default:
                return false;
        }
    }
}