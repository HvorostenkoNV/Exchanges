<?php
namespace Main\Exchange\DataProcessors;

class Manager
{
	public static function getProcessor(string $processorName) : ?Processor
	{
		return NULL;
	}

	public static function getProcessorsList() : array
	{
		return [];
	}
}