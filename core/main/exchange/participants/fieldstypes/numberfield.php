<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "number" field type
 *
 * @package exchange_exchange
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
        $valuePrintable = var_export($value, true);

        switch (gettype($value))
        {
            case 'string':
                if (!is_numeric($value))
                {
                    throw new DomainException("unable convert \"$valuePrintable\" to number");
                }

                return strpos($value, '.') !== false
                    ? (float) $value
                    : (int) $value;
            case 'integer':
            case 'double':
                return $value;
            case 'boolean':
                return $value ? 1 : 0;
            case 'NULL':
                return null;
            case 'array':
            case 'object':
            case 'resource':
            default:
                throw new DomainException("unable convert \"$valuePrintable\" to number");
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
        $valuePrintable = var_export($value, true);

        switch (gettype($value))
        {
            case 'integer':
            case 'double':
                return (string) $value;
            case 'NULL':
                return '';
            case 'string':
            case 'boolean':
            case 'array':
            case 'object':
            case 'resource':
            default:
                throw new DomainException("unable convert \"$valuePrintable\" for print");
        }
    }
    /** **********************************************************************
     * get random value
     *
     * @return  mixed                       random value
     ************************************************************************/
    public function getRandomValue()
    {
        if (rand(0, 3) === 0)
        {
            return 0;
        }

        return rand(1, getrandmax());
    }
}