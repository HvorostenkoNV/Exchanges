<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Data;

use
    InvalidArgumentException,
    Main\Data\SetData,
    Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Participants set
 * Collection of Participant objects
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
     ************************************************************************/
    public function delete($object) : void
    {
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
            $needClass = Participant::class;
            throw new InvalidArgumentException("value must be instance of \"$needClass\"");
        }

        parent::push($object);
    }
}