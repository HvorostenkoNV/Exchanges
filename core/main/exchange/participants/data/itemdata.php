<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    DomainException,
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Participants\Fields\Field;
/** ***********************************************************************************************
 * Participants item map data, item fields data.
 * Map data, collection of key => values
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ItemData extends MapData
{
    /** **********************************************************************
     * delete value by key
     *
     * @param   Field $key                  value key
     ************************************************************************/
    public function delete($key) : void
    {
        parent::delete($key);
    }
    /** **********************************************************************
     * get value by key
     *
     * @param   Field $key                  value key
     * @return  mixed                       value
     ************************************************************************/
    public function get($key)
    {
        return parent::get($key);
    }
    /** **********************************************************************
     * get map keys
     *
     * @return  Field[]                     map keys
     ************************************************************************/
    public function getKeys() : array
    {
        return parent::getKeys();
    }
    /** **********************************************************************
     * check map has key
     *
     * @param   Field $key                  key to check
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
     * @param   Field   $key                value key
     * @param   mixed   $value              value
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof Field)
        {
            $needClass = Field::class;
            throw new InvalidArgumentException("key must be instance of \"$needClass\"");
        }

        try
        {
            $validatedValue = $key->getFieldType()->validateValue($value);
            parent::set($key, $validatedValue);
        }
        catch (DomainException $exception)
        {
            $error = $exception->getMessage();
            throw new InvalidArgumentException("value validation failed with error \"$error\"");
        }
    }
}