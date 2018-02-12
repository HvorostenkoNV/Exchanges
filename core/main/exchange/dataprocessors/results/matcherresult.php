<?php
namespace Main\Exchange\DataProcessors\Results;

class MatcherResult implements Result
{
	public function __construct(array $data)
	{

	}

	public function getGeneralId(string $participantClassName, int $id) : int
	{
		return 0;
	}

	public function getParticipantId(int $generalId) : int
	{
		return 0;
	}
}