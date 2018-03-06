<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
	Main\Exchange\Participants\Data\ProvidedData,
	Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Participant UsersAD
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersAD extends AbstractParticipants
{
	/** **********************************************************************
	 * read provided data and get it
	 * @return  ProvidedData    data
	 * TODO
	 ************************************************************************/
	protected function readProvidedData() : ProvidedData
	{
		return new ProvidedData;
	}
	/** **********************************************************************
	 * write delivered data
	 * @param   DeliveredData   $data   data to write
	 * @return  bool                    process result
	 * TODO
	 ************************************************************************/
	protected function writeDeliveredData(DeliveredData $data) : bool
	{
		return false;
	}
}