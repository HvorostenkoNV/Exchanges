<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "boolean" field type
 *
 * @package exchange_exchange_participants
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
            is_null($value)     ||
            $value  === false   ||
            $value  === 0       ||
            $value  === '0'     ||
            $value  === 'N'     ||
            $value  === 'n'
        )
        {
            return false;
        }

        $valuePrintable = var_export($value, true);
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
        switch (gettype($value))
        {
            case 'boolean':
                return $value ? 'Y' : 'N';
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
        return rand(1, 2) == 2;
    }
}