<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use
    DomainException,
    InvalidArgumentException;
/** ***********************************************************************************************
 * Participant "array of numbers" field type
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ArrayOfNumbersField extends AbstractField
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
        $result = [];

        try
        {
            $field = Manager::getField('number');
            foreach ((array) $value as $item)
            {
                try
                {
                    $result[] = $field->validateValue($item);
                }
                catch (DomainException $exception)
                {

                }
            }
            $result = array_filter($result, function($item)
            {
                return !is_null($item);
            });
        }
        catch (InvalidArgumentException $exception)
        {

        }

        return array_values($result);
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
        $result = [];

        try
        {
            $field = Manager::getField('number');
            foreach ((array) $value as $item)
            {
                try
                {
                    $result[] = $field->convertValueForPrint($item);
                }
                catch (DomainException $exception)
                {

                }
            }
            $result = array_filter($result, function($item)
            {
                return is_string($item) && strlen($item) > 0;
            });
        }
        catch (InvalidArgumentException $exception)
        {

        }

        return array_values($result);
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
            $field      = Manager::getField('number');
            $randomSize = rand(1, 15);
            $result     = [];

            for ($index = $randomSize; $index > 0; $index--)
            {
                $result[] = $field->getRandomValue();
            }

            return $result;
        }
        catch (InvalidArgumentException $exception)
        {
            return [];
        }
    }
}