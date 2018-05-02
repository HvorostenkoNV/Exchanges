<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class Users1C extends AbstractParticipants
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  ProvidedData                data
     * TODO
     ************************************************************************/
    protected function readProvidedData() : ProvidedData
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   DeliveredData   $data       data to write
     * @return  bool                        process result
     * TODO
     ************************************************************************/
    protected function writeDeliveredData(DeliveredData $data) : bool
    {
        return false;
    }
}