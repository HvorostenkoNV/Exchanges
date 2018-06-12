<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Application participant UsersBitrix
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersBitrix extends AbstractParticipant
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
     * @param   DataForDelivery $data       data to write
     * @return  bool                        process result
     * TODO
     ************************************************************************/
    protected function provideDataForDelivery(DataForDelivery $data) : bool
    {
        return false;
    }
}