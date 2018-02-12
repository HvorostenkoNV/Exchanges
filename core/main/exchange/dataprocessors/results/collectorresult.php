<?php
namespace Main\Exchange\DataProcessors\Results;

class CollectorResult implements Result
{
	public function __construct(array $data)
	{

	}

	public function getData(string $participantClassName) : array
	{
		return [];
	}
}