<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * Unknown participant item exception
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UnknownParticipantItemException extends Exception implements Throwable
{
    private
        $participantCode    = '',
        $itemId             = '';
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
    /** **********************************************************************
     * set participant item ID
     *
     * @param   string $itemId              item ID
     * @return  void
     ************************************************************************/
    public function setParticipantItemId(string $itemId) : void
    {
        $this->itemId = $itemId;
    }
    /** **********************************************************************
     * get participant item ID
     *
     * @return  string                      item ID
     ************************************************************************/
    public function getParticipantItemId() : string
    {
        return $this->itemId;
    }
}