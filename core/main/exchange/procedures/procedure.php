<?php
namespace Main\Exchange\Procedures;

interface Procedure
{
	public function getParticipants() : array;
}