<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Data;

use
    InvalidArgumentException,
    Main\Data\SetData,
    Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Procedures collection, collection type of "First In, First Out"
 * Collection of Participants objects
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantsSet extends SetData
{
    /** **********************************************************************
     * get current item
     *
     * @return  Participant|null            current item or null
     ************************************************************************/
    public function current()
    {
        return parent::current();
    }
    /** **********************************************************************
     * drop item from set
     *
     * @param   Participant $object         item for drop
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function delete($object) : void
    {
        if (!$object instanceof Participant)
        {
            $needClassName = Participant::class;
            throw new InvalidArgumentException("value must be instance of \"$needClassName\"");
        }

        parent::delete($object);
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   Participant $object         pushed item
     * @throws  InvalidArgumentException    object is not Field
     ************************************************************************/
    public function push($object) :void
    {
        if (!$object instanceof Participant)
        {
            $needClassName = Participant::class;
            throw new InvalidArgumentException("value must be instance of \"$needClassName\"");
        }

        parent::push($object);
    }
}