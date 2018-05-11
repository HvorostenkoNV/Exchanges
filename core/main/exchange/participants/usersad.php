<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Application participant UsersAD
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersAD extends AbstractParticipants
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  Data                    data
     * TODO
     ************************************************************************/
    protected function readProvidedData() : Data
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   Data    $data           data to write
     * @return  bool                    process result
     * TODO
     ************************************************************************/
    protected function provideDataForDelivery(Data $data) : bool
    {
        return false;
    }
}