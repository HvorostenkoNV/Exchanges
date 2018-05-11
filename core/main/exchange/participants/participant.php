<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Fields\FieldsMap,
    Main\Exchange\Participants\Data\Data as ParticipantData;
/** ***********************************************************************************************
 * Application participant interface
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
interface Participant
{
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsMap                   fields params
     ************************************************************************/
    public function getFields() : FieldsMap;
    /** **********************************************************************
     * get participant provided raw data
     *
     * @return  ParticipantData             provided data
     ************************************************************************/
    public function getProvidedData() : ParticipantData;
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   ParticipantData $data       data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    public function deliveryData(ParticipantData $data) : bool;
}