<?php
namespace Main\Exchange\DataProcessors;

use
	Main\Exchange\
	{
		DataProcessors\Results\Result,
		Procedures\Procedure
	};

interface Processor
{
	public function __construct(Procedure $procedure);
	public function getProcedure() : Procedure;
	public function getResult() : ?Result;
	public function setResult(Result $result) : void;
	public function process() : void;
}