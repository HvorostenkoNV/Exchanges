<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant "string" field type
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class StringField extends AbstractField
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
                return strlen($value) > 0
                    ? $value
                    : null;
            case 'integer':
            case 'double':
                return (string) $value;
            case 'boolean':
                return $value ? 'Y' : 'N';
            case 'NULL':
                return null;
            case 'array':
            case 'object':
            case 'resource':
            default:
                throw new DomainException("unable convert \"$valuePrintable\" to string");
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
            case 'string':
                return $value;
            case 'NULL':
                return '';
            case 'integer':
            case 'double':
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
            return null;
        }

        $characters         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersArray    = str_split($characters);
        $randomLength       = rand(1, count($charactersArray));
        $result             = '';

        for ($index = $randomLength; $index > 0; $index--)
        {
            $result .= $charactersArray[array_rand($charactersArray)];
        }

        return $result;
    }
}