<?php
declare(strict_types=1);

namespace Main\Exchange;

use
    Main\Singleton,
    Main\Helpers\Logger,
    Main\Data\MapData,
    Main\Exchange\Procedures\Manager        as ProceduresManager,
    Main\Exchange\DataProcessors\Manager    as ProcessorsManager;
/** ***********************************************************************************************
 * Exchange class, application exchange entrance point
 *
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
     ************************************************************************/
    public function run() : void
    {
        Logger::getInstance()->addNotice('Exchange process start');

        $filter = new MapData;
        $filter->set('ACTIVITY', true);
        $proceduresSet = ProceduresManager::getProcedures($filter);

        while ($proceduresSet->valid())
        {
            $procedure  = $proceduresSet->current();
            $collector  = ProcessorsManager::getCollector($procedure);
            $matcher    = ProcessorsManager::getMatcher($procedure);
            $combiner   = ProcessorsManager::getCombiner($procedure);
            $provider   = ProcessorsManager::getProvider($procedure);

            $collectedData  = $collector->collectData();
            $matchedData    = $matcher->matchItems($collectedData);
            $combinedData   = $combiner->combineItems($matchedData);
            $provider->provideData($combinedData);

            $proceduresSet->next();
        }
    }
}