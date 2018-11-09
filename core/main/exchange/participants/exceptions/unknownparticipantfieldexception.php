<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * Unknown participant field exception
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UnknownParticipantFieldException extends Exception implements Throwable
{
    private
        $participantCode    = '',
        $fieldName          = '';
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
     * set participant field name
     *
     * @param   string $fieldName           field name
     * @return  void
     ************************************************************************/
    public function setParticipantFieldName(string $fieldName) : void
    {
        $this->fieldName = $fieldName;
    }
    /** **********************************************************************
     * get participant field name
     *
     * @return  string                      field name
     ************************************************************************/
    public function getParticipantFieldName() : string
    {
        return $this->fieldName;
    }
}