<?php
namespace Main\Exchange\Procedures;

interface Procedure
{
	public function __construct(array $participants, Params $params);
	public function getParticipants() : array;
	public function getParams() : Params;
}