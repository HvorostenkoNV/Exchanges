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
     * @return  void
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
     * @return  void
     * @throws  InvalidArgumentException    incorrect key or value
     ************************************************************************/
    public function set($key, $value) : void
    {
        if (!$key instanceof Field)
        {
            $needClass  = Field::class;
            $getedType  = gettype($key);
            $getedValue = $getedType == 'object' ? get_class($key) : $getedType;

            throw new InvalidArgumentException("key must be instance of \"$needClass\", caught \"$getedValue\"");
        }
        if ($key->getParam('required') && $this->isEmptyValue($value))
        {
            throw new InvalidArgumentException('value is empty while field is required');
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
    /** **********************************************************************
     * check value is empty
     *
     * @param   mixed $value                value
     * @return  bool                        value is empty
     ************************************************************************/
    private function isEmptyValue($value) : bool
    {
        switch (gettype($value))
        {
            case 'string':
                return strlen($value) > 0 ? false : true;
            case 'array':
                foreach ($value as $arrayValue)
                {
                    if (!$this->isEmptyValue($arrayValue))
                    {
                        return false;
                    }
                }

                return true;
            case 'NULL':
                return true;
            default:
                return false;
        }
    }
}