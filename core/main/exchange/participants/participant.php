<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Fields\FieldsSet,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Application participant interface
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
interface Participant
{
    /** **********************************************************************
     * get participant code
     *
     * @return  string                      participant code
     ************************************************************************/
    public function getCode() : string;
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsSet                   fields params
     ************************************************************************/
    public function getFields() : FieldsSet;
    /** **********************************************************************
     * get participant provided raw data
     *
     * @return  ProvidedData                provided data
     ************************************************************************/
    public function getProvidedData() : ProvidedData;
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   DataForDelivery $data       data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    public function deliveryData(DataForDelivery $data) : bool;
}