<?php
namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;

class Manager
{
	public static function getCollector(Procedure $procedure) : Collector
	{
		return new Collector($procedure);
	}

	public static function getMatcher(Procedure $procedure) : Matcher
	{
		return new Matcher($procedure);
	}

	public static function getCombiner(Procedure $procedure) : Combiner
	{
		return new Combiner($procedure);
	}

	public static function getProvider(Procedure $procedure) : Provider
	{
		return new Provider($procedure);
	}
}