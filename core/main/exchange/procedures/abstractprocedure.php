<?php
namespace Main\Exchange\Procedures;

abstract class AbstractProcedure implements Procedure
{
	private
		$participants   = [],
		$params         = NULL;

	public function __construct(array $participants, Params $params)
	{

	}

	public function getParticipants() : array
	{
		return $this->participants;
	}

	public function getParams() : Params
	{
		return new Params([]);
	}
}