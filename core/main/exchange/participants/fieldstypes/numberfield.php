<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "number" field type
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class NumberField extends AbstractField
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
                    return strpos($value, '.') === false
                        ? (int) $value
                        : (float) $value;
                }

                break;
            case 'integer':
            case 'double':
                return $value;
            case 'boolean':
                return $value ? 1 : 0;
            case 'NULL':
                return null;
            default:
        }

        $valuePrintable = var_export($value, true);
        throw new DomainException("unable convert \"$valuePrintable\" to number");
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
            case 'integer':
            case 'double':
                return (string) $value;
            case 'NULL':
                return '';
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
        return rand(1, 4) == 4
            ? 0
            : rand(1, getrandmax());
    }
}