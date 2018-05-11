<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    DomainException,
    InvalidArgumentException,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field;
/** ***********************************************************************************************
 * Participants field value
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldValue
{
    private
        $field          = null,
        $value          = null,
        $printableValue = null;
    /** **********************************************************************
     * construct
     *
     * @param   mixed   $value              field value
     * @param   Field   $field              field
     * @throws  DomainException             impossible value
     ************************************************************************/
    final public function __construct($value, Field $field)
    {
        try
        {
            $fieldType          = $field->getParam('type');
            $fieldTypeObject    = FieldsTypesManager::getField($fieldType);

            $this->value            = $fieldTypeObject->validateValue($value);
            $this->printableValue   = $fieldTypeObject->convertValueForPrint($this->value);
            $this->field            = $field;
        }
        catch (DomainException $exception)
        {
            throw $exception;
        }
        catch (InvalidArgumentException $exception)
        {
            throw new DomainException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * get field
     *
     * @return  Field                       field
     ************************************************************************/
    final public function getField()
    {
        return $this->field;
    }
    /** **********************************************************************
     * get value
     *
     * @return  mixed                       field value
     ************************************************************************/
    final public function getValue()
    {
        return $this->value;
    }
    /** **********************************************************************
     * get printable value
     *
     * @return  mixed                       field value
     ************************************************************************/
    final public function getPrintableValue()
    {
        return $this->printableValue;
    }
}