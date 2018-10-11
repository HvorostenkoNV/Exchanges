<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use
    DomainException,
    InvalidArgumentException;
/** ***********************************************************************************************
 * Participant "array" field type
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ArrayField extends AbstractField
{
    /** **********************************************************************
     * validate value
     *
     * @param   mixed   $value              value
     * @return  mixed                       validated value
     * @throws  DomainException             bad validating result
     ************************************************************************/
    public function validateValue($value)
    {
        switch (gettype($value))
        {
            case 'array':
                return $value;
            default:
                return [$value];
        }
    }
    /** **********************************************************************
     * convert value for print
     *
     * @param   mixed   $value              value
     * @return  mixed                       converted value
     * @throws  DomainException             bad converting result
     ************************************************************************/
    public function convertValueForPrint($value)
    {
        return $value;
    }
    /** **********************************************************************
     * get random value
     *
     * @return  mixed                       random value
     ************************************************************************/
    public function getRandomValue()
    {
        if (rand(1, 4) == 4)
        {
            return [];
        }

        try
        {
            $availableFieldsTypes   = Manager::getAvailableFieldsTypes();
            $randomSize             = rand(1, 15);
            $result                 = [];

            for ($index = $randomSize; $index > 0; $index--)
            {
                $randomFieldType    = $availableFieldsTypes[array_rand($availableFieldsTypes)];
                $field              = Manager::getField($randomFieldType);
                $result[]           = $field->getRandomValue();
            }

            return $result;
        }
        catch (InvalidArgumentException $exception)
        {
            return [];
        }
    }
}