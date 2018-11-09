<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * Unknown participant exception
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UnknownParticipantException extends Exception implements Throwable
{
    private $participantCode = '';
    /** **********************************************************************
     * set participant code
     *
     * @param   string $participantCode     participant code
     * @return  void
     ************************************************************************/
    public function setParticipantCode(string $participantCode) : void
    {
        $this->participantCode = $participantCode;
    }
    /** **********************************************************************
     * get participant code
     *
     * @return  string                      participant code
     ************************************************************************/
    public function getParticipantCode() : string
    {
        return $this->participantCode;
    }
}