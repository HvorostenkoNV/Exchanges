<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Data;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData,
    Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Procedures collection, collection type of "First In, First Out"
 * Collection of Participants objects
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantsQueue extends QueueData
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  Participant                 data
     * @throws  RuntimeException            if no data for extract
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * push data to the end
     *
     * @param   Participant $data           data
     * @throws  InvalidArgumentException    pushed data is not Procedure
     ************************************************************************/
    public function push($data) : void
    {
        if (!$data instanceof Participant)
        {
            $needClassName = Participant::class;
            throw new InvalidArgumentException("pushed data must be instance of $needClassName");
        }

        parent::push($data);
    }
}