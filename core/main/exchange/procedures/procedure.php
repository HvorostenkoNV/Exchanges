<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Procedures interface
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
interface Procedure
{
	/** **********************************************************************
	 * get participants array
	 * @return  Participant[]   participants array
	 ************************************************************************/
	public function getParticipants() : array;
}