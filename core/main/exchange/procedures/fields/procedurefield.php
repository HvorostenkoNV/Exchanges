<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Fields;

use
    InvalidArgumentException,
    Main\Data\SetData;
/** ***********************************************************************************************
 * Procedure field class
 * Display object as set of different participants fields like ONE procedure field
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class ProcedureField extends SetData
{
    /** **********************************************************************
     * get current item
     *
     * @return  ParticipantField|null       current item or null
     ************************************************************************/
    public function current()
    {
        return parent::current();
    }
    /** **********************************************************************
     * drop item from set
     *
     * @param   ParticipantField $object    item for drop
     ************************************************************************/
    public function delete($object) : void
    {
        parent::delete($object);
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   ParticipantField $object    pushed item
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function push($object) :void
    {
        if (!$object instanceof ParticipantField)
        {
            $needClass = ParticipantField::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::push($object);
    }
}