<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\DataProcessors\Results\CollectorResult;

class Matcher extends AbstractProcessor
{
	protected
		$collectedData = NULL;

	public function setCollectedData(CollectorResult $data) : void
	{
		$this->collectedData = $data;
	}

	public function process() : void
	{

	}
}