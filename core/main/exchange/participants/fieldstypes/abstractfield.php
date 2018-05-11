<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\FieldsTypes;

use DomainException;
/** ***********************************************************************************************
 * Participant abstract field type
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractField implements Field
{
    /** **********************************************************************
     * validate value
     *
     * @param   mixed   $value              value
     * @return  mixed                       validated value
     * @throws  DomainException             bad validating result
     ************************************************************************/
    abstract public function validateValue($value);
    /** **********************************************************************
     * convert value for print
     *
     * @param   mixed   $value              value
     * @return  mixed                       converted value
     * @throws  DomainException             bad converting result
     ************************************************************************/
    abstract public function convertValueForPrint($value);
    /** **********************************************************************
     * get random value
     *
     * @return  mixed                       random value
     ************************************************************************/
    abstract public function getRandomValue();
}