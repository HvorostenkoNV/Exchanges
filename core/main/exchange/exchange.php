<?php
declare(strict_types=1);

namespace Main\Exchange;

use
	Main\Singleton,
	Main\Helpers\Logger,
	Main\Exchange\Procedures\Manager        as ProceduresManager,
	Main\Exchange\DataProcessors\Manager    as ProcessorsManager;
/** ***********************************************************************************************
 * Exchange class, exchange entrance point
 * @package exchange_exchange
 * @method  static Exchange getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Exchange
{
	use Singleton;
	/** **********************************************************************
	 * constructor
	 ************************************************************************/
	private function __construct()
	{
		Logger::getInstance()->addNotice('Exchange object created');
	}
	/** **********************************************************************
	 * run exchange process
	 * TODO
	 ************************************************************************/
	public function run() : void
	{
		Logger::getInstance()->addNotice('Exchange process start');
/*
		foreach( ProceduresManager::getProceduresList() as $procedure )
		{
			$collector  = ProcessorsManager::getCollector($procedure);
			$matcher    = ProcessorsManager::getMatcher($procedure);
			$combiner   = ProcessorsManager::getCombiner($procedure);
			$provider   = ProcessorsManager::getProvider($procedure);

			$collector->process();
			$matcher->setCollectedData($collector->getCollectedData());
			$matcher->process();
			$combiner->setMatchedData($matcher->getMatchedData());
			$combiner->process();
			$provider->setCombinedData($combiner->getCombinedData());
			$provider->process();
		}
*/
	}
}