<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Data;

use
    InvalidArgumentException,
    Main\Data\SetData,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Procedures set
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
     * @param   Procedure $object           item for drop
     * @return  void
     ************************************************************************/
    public function delete($object) : void
    {
        parent::delete($object);
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   Procedure $object           pushed item
     * @return  void
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function push($object) :void
    {
        if (!$object instanceof Procedure)
        {
            $needClass = Procedure::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::push($object);
    }
}