<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;

abstract class AbstractProcessor implements Processor
{
	private $procedure = NULL;

	final public function __construct(Procedure $procedure)
	{
		// TODO
		$this->procedure = $procedure;
	}

	final public function getProcedure() : Procedure
	{
		return $this->procedure;
	}

	abstract public function process() : void;
}