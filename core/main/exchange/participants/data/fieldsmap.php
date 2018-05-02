<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    InvalidArgumentException,
    Main\Data\MapData;
/** ***********************************************************************************************
 * Participant fields map data
 * Map data, collection of key => values
 * Keys are fields names
 * Values are Field objects
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldsMap extends MapData
{
    /** **********************************************************************
     * construct
     *
     * @param   Field[] $data               array of fields objects
     * @throws  InvalidArgumentException    incorrect array
     ************************************************************************/
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value)
        {
            if (!$value instanceof Field)
            {
                $needClassName = Field::class;
                throw new InvalidArgumentException("incorrect array data: data values must be instance of $needClassName");
            }
            if (!is_string($key) || strlen($key) <= 0 || $key != $value->getName())
            {
                throw new InvalidArgumentException('incorrect array data: data keys must be fields objects names');
            }
        }

        parent::__construct($data);
    }
    /** **********************************************************************
     * drop field from map by field name
     *
     * @param   string  $key                field name
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get field by field name
     *
     * @param   string  $key                field name
     * @return  Field                       field
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys, fields names
     *
     * @return  string[]                    map keys, fields names
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   string  $key                key to check
     * @return  bool                        map has key
     ************************************************************************/
    public function hasKey($key) : bool
    {
        return parent::hasKey($key);
    }
    /** **********************************************************************
     * check map has field
     *
     * @param   Field   $value              field object
     * @return  bool                        map has field
     ************************************************************************/
    public function hasValue($value) : bool
    {
        return parent::hasValue($value);
    }
    /** **********************************************************************
     * attach field by field name
     *
     * @param   string  $key                field name
     * @param   Field   $value              field object
     * @throws  InvalidArgumentException    incorrect key or value type
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$value instanceof Field)
        {
            $needClassName = Field::class;
            throw new InvalidArgumentException("value must be instance of $needClassName");
        }
        if (!is_string($key) || strlen($key) <= 0 || $key != $value->getName())
        {
            throw new InvalidArgumentException('key must be field object name');
        }

        parent::set($key, $value);
    }
}