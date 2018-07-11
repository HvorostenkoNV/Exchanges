<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\FieldsSet,
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Participant stub, empty class for testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantStub implements Participant
{
    /** **********************************************************************
     * get participant code
     *
     * @return  string                      participant code
     ************************************************************************/
    public function getCode() : string
    {
        return '';
    }
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsSet                   fields params
     ************************************************************************/
    public function getFields() : FieldsSet
    {
        return new FieldsSet;
    }
    /** **********************************************************************
     * get participant provided raw data
     *
     * @return  Data                        provided data
     ************************************************************************/
    public function getProvidedData() : Data
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   Data $data                  data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    public function deliveryData(Data $data) : bool
    {
        return false;
    }
}