<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
	Throwable,
	DomainException,
	Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Procedures abstract class
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractProcedure implements Procedure
{
	protected   $participantsClasses    = [];
	private     $participants           = [];
	/** **********************************************************************
	 * construct
	 * @throws  DomainException     problems with getting participants
	 ************************************************************************/
	final public function __construct()
	{
		if( count($this->participantsClasses) <= 0 )
			throw new DomainException('Class property "participantsClasses" is empty');

		foreach( $this->participantsClasses as $className )
		{
			try                         {$this->participants[] = new $className;}
			catch( Throwable $error )   {throw new DomainException($error->getMessage());}
		}
	}
	/** **********************************************************************
	 * get participants array
	 * @return  Participant[]   participants array
	 ************************************************************************/
	public function getParticipants() : array
	{
		return $this->participants;
	}
}