<?php
namespace Main\Exchange\DataProcessors;

use
	Main\Exchange\
	{
		DataProcessors\Results\Result,
		Procedures\Procedure
	};

abstract class AbstractProcessor implements Processor
{
	private
		$procedure  = NULL,
		$result     = NULL;

	final public function __construct(Procedure $procedure)
	{
		$this->procedure = $procedure;
	}

	final public function getProcedure() : Procedure
	{
		return $this->procedure;
	}

	final public function getResult() : ?Result
	{
		return $this->result;
	}

	final protected function setResult(Result $result) : void
	{
		$this->result = $result;
	}

	abstract public function process() : void;
}