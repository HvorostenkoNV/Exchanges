<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Fields;

use
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field;
/** ***********************************************************************************************
 * Participant field class
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantField
{
    private
        $participant    = null,
        $field          = null;
    /** **********************************************************************
     * construct
     *
     * @param   Participant $participant    participant
     * @param   Field       $field          field
     ************************************************************************/
    public function __construct(Participant $participant, Field $field)
    {
        $this->participant  = $participant;
        $this->field        = $field;
    }
    /** **********************************************************************
     * get participant
     *
     * @return  Participant                 participant
     ************************************************************************/
    public function getParticipant() : Participant
    {
        return $this->participant;
    }
    /** **********************************************************************
     * get field
     *
     * @return  Field                       field
     ************************************************************************/
    public function getField() : Field
    {
        return $this->field;
    }
}