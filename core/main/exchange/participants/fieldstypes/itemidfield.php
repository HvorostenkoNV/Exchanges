<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "item ID" field type
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ItemIdField extends AbstractField
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
            case 'string':
                if (is_numeric($value))
                {
                    $value = strpos($value, '.') === false
                        ? (int) $value
                        : (float) $value;
                }

                if (is_int($value) && $value > 0)
                {
                    return $value;
                }
                if (is_string($value) && strlen($value) > 0)
                {
                    return $value;
                }

                break;
            case 'integer':
                if ($value > 0)
                {
                    return $value;
                }

                break;
            default:
        }

        $valuePrintable = var_export($value, true);
        throw new DomainException("unable convert \"$valuePrintable\" to item ID");
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
        switch (gettype($value))
        {
            case 'string':
                if (is_numeric($value))
                {
                    $value = strpos($value, '.') === false
                        ? (int) $value
                        : (float) $value;
                }

                if (is_int($value) && $value > 0)
                {
                    return (string) $value;
                }
                if (is_string($value) && strlen($value) > 0)
                {
                    return $value;
                }

                break;
            case 'integer':
                if ($value > 0)
                {
                    return (string) $value;
                }

                break;
            default:
        }

        $valuePrintable = var_export($value, true);
        throw new DomainException("unable convert \"$valuePrintable\" for print");
    }
    /** **********************************************************************
     * get random value
     *
     * @return  mixed                       random value
     ************************************************************************/
    public function getRandomValue()
    {
        if (rand(1, 2) == 1)
        {
            $stringField = new StringField;
            while (true)
            {
                $randomString = $stringField->getRandomValue();
                if (is_string($randomString) && strlen($randomString) > 0 && !is_numeric($randomString))
                {
                    return $randomString;
                }
            }
        }

        return rand(1, getrandmax());
    }
}