<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\DataProcessors\Results\MatcherResult;

class Combiner
{
	protected
		$matchedData = NULL;

	public function setMatchedData(MatcherResult $data) : void
	{
		$this->matchedData = $data;
	}

	public function process() : void
	{

	}
}