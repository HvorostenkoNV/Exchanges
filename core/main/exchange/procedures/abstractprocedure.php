<?php
namespace Main\Exchange\Procedures;

use
	Throwable,
	DomainException;

abstract class AbstractProcedure implements Procedure
{
	protected   $participantsClasses    = [];
	private     $participants           = [];
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
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
	/* -------------------------------------------------------------------- */
	/* ------------------------- get participants ------------------------- */
	/* -------------------------------------------------------------------- */
	public function getParticipants() : array
	{
		return $this->participants;
	}
}