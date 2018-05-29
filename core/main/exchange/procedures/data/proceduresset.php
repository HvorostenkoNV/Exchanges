<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Data;

use
    InvalidArgumentException,
    Main\Data\SetData,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Procedures collection, collection type of "First In, First Out"
 * Collection of Procedure objects
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class ProceduresSet extends SetData
{
    /** **********************************************************************
     * get current item
     *
     * @return  Procedure|null              current item or null
     ************************************************************************/
    public function current()
    {
        return parent::current();
    }
    /** **********************************************************************
     * drop item from set
     *
     * @param   Procedure   $object         item for drop
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function delete($object) : void
    {
        if (!$object instanceof Procedure)
        {
            $needClassName = Procedure::class;
            throw new InvalidArgumentException("value must be instance of \"$needClassName\"");
        }

        parent::delete($object);
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   Procedure   $object         pushed item
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function push($object) :void
    {
        if (!$object instanceof Procedure)
        {
            $needClassName = Procedure::class;
            throw new InvalidArgumentException("value must be instance of \"$needClassName\"");
        }

        parent::push($object);
    }
}