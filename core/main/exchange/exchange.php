<?php
namespace Main\Exchange;

use
	Main\Singltone,
	Main\Helpers\Logger,
	Main\Exchange\Procedures\Manager        as ProceduresManager,
	Main\Exchange\DataProcessors\Manager    as ProcessorsManager;

class Exchange
{
	use Singltone;
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
	private function __construct()
	{
		Logger::getInstance()->addNotice('Exchange object created');
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------------- run -------------------------------- */
	/* -------------------------------------------------------------------- */
	public function run() : void
	{
		Logger::getInstance()->addNotice('Exchange process start');

		foreach( ProceduresManager::getProceduresList() as $procedure )
		{
			$collector  = ProcessorsManager::getCollector($procedure);
			$matcher    = ProcessorsManager::getMatcher($procedure);
			$combiner   = ProcessorsManager::getCombiner($procedure);
			$provider   = ProcessorsManager::getProvider($procedure);
			// TODO
/*
			$collector->process();
			$matcher->setCollectedData($collector->getCollectedData());
			$matcher->process();
			$combiner->setMatchedData($matcher->getMatchedData());
			$combiner->process();
			$provider->setCombinedData($combiner->getCombinedData());
			$provider->process();
*/
		}
	}
}