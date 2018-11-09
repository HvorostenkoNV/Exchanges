<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Fields;

use
    InvalidArgumentException,
    Main\Data\SetData;
/** ***********************************************************************************************
 * Procedures fields set
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class FieldsSet extends SetData
{
    /** **********************************************************************
     * get current item
     *
     * @return Field|null                   current item or null
     ************************************************************************/
    public function current()
    {
        return parent::current();
    }
    /** **********************************************************************
     * drop item from set
     *
     * @param   Field $object               item for drop
     * @return  void
     ************************************************************************/
    public function delete($object) : void
    {
        parent::delete($object);
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   Field $object               pushed item
     * @return  void
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function push($object) :void
    {
        if (!$object instanceof Field)
        {
            $needClass = Field::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::push($object);
    }
}