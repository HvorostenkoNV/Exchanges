<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Fields\FieldsSet,
    Main\Exchange\Participants\Data\Data;
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
     * @return  Data                        provided data
     ************************************************************************/
    public function getProvidedData() : Data;
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   Data $data                  data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    public function deliveryData(Data $data) : bool;
}