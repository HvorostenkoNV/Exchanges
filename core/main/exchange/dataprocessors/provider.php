<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\DataProcessors\Results\CombinerResult;

class Provider
{
	protected
		$combinedData = NULL;

	public function setCombinedData(CombinerResult $data) : void
	{
		$this->combinedData = $data;
	}

	public function process() : void
	{

	}
}