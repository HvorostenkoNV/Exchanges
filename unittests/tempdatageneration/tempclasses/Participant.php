<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration\TempClasses;

use
    Main\Exchange\Participants\AbstractParticipant as SystemAbstractParticipant,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Fields\FieldsSet;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class Participant extends SystemAbstractParticipant
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @param   FieldsSet $fields           participant fields set
     * @return  Data                        data
     ************************************************************************/
    protected function readProvidedData(FieldsSet $fields) : Data
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   Data $data                  data to write
     * @return  bool                        process result
     ************************************************************************/
    protected function provideDataForDelivery(Data $data) : bool
    {
        return false;
    }
}