<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Results;

use
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Collected participants data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class CollectedData implements Result
{
    /** **********************************************************************
     * set participant data
     *
     * @param   Participant     $participant        participant
     * @param   ProvidedData    $data               data
     * //TODO
     ************************************************************************/
    public function setData(Participant $participant, ProvidedData $data) : void
    {

    }
    /** **********************************************************************
     * get participant data
     *
     * @param   Participant         $participant    participant
     * @return  ProvidedData|null   $data           data
     * //TODO
     ************************************************************************/
    public function getData(Participant $participant) : ?ProvidedData
    {
        return new ProvidedData;
    }
}