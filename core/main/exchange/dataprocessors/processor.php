<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;

interface Processor
{
	public function __construct(Procedure $procedure);
	public function getProcedure() : Procedure;
	public function process() : void;
}