<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use Main\Exchange\Procedures\Data\ParticipantsQueue;
/** ***********************************************************************************************
 * Application procedure interface
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
interface Procedure
{
    /** **********************************************************************
     * get participants
     *
     * @return  ParticipantsQueue           participants
     ************************************************************************/
    public function getParticipants() : ParticipantsQueue;
}