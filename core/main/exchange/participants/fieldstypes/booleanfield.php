<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "boolean" field type
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class BooleanField extends AbstractField
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

        if
        (
            $value  === true    ||
            $value  === 1       ||
            $value  === '1'     ||
            $value  === 'Y'     ||
            $value  === 'y'
        )
        {
            return true;
        }
        if
        (
            $value  === false   ||
            $value  === 0       ||
            $value  === '0'     ||
            $value  === 'N'     ||
            $value  === 'n'
        )
        {
            return false;
        }

        throw new DomainException("unable convert \"$valuePrintable\" to boolean");
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
            case 'boolean':
                return $value ? 'Y' : 'N';
            case 'NULL':
                return '';
            case 'integer':
            case 'double':
            case 'string':
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
        return rand(0, 1) === 0;
    }
}