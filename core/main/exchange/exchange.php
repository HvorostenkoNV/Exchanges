<?php
declare(strict_types=1);

namespace Main\Exchange;

use
    Throwable,
    SplFileInfo,
    Main\Singleton,
    Main\Helpers\Logger,
    Main\Helpers\Config,
    Main\Data\MapData,
    Main\Exchange\Procedures\Manager as ProceduresManager,
    Main\Exchange\DataProcessors\ProcedureItemsMap,
    Main\Exchange\DataProcessors\ProcedureData,
    Main\Exchange\DataProcessors\Collector,
    Main\Exchange\DataProcessors\Matcher,
    Main\Exchange\DataProcessors\Combiner,
    Main\Exchange\DataProcessors\Provider;
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

    private $processTempFilePath = '';
    /** **********************************************************************
     * constructor
     ************************************************************************/
    private function __construct()
    {
        $logger                 = Logger::getInstance();
        $config                 = Config::getInstance();
        $tempFolderParam        = $config->getParam('structure.tempFolder');
        $processTempFileName    = 'exchangeProcessCondition';

        $this->processTempFilePath =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $processTempFileName;

        $logger->addNotice('Exchange object created');
    }
    /** **********************************************************************
     * run exchange process
     *
     * @return void
     ************************************************************************/
    public function run() : void
    {
        $logger = Logger::getInstance();

        if ($this->checkAlreadyRun())
        {
            return;
        }

        try
        {
            $this->markAlreadyRun(true);
            $logger->addNotice('Exchange process start');

            $filter = new MapData;
            $filter->set('ACTIVITY', true);
            $proceduresSet = ProceduresManager::getProcedures($filter);

            while ($proceduresSet->valid())
            {
                $procedure  = $proceduresSet->current();
                $itemsMap   = new ProcedureItemsMap($procedure);
                $itemsData  = new ProcedureData($procedure);

                $collector  = new Collector($procedure);
                $matcher    = new Matcher($procedure, $itemsMap, $itemsData);
                $combiner   = new Combiner($procedure, $itemsData);
                $provider   = new Provider($procedure, $itemsMap);

                $collectedData  = $collector->collectData();
                $matchedData    = $matcher->matchItems($collectedData);
                $combinedData   = $combiner->combineItems($matchedData);
                $provider->provideData($combinedData);

                $proceduresSet->next();
            }

            $this->markAlreadyRun(false);
            $logger->write();
        }
        catch (Throwable $exception)
        {
            $error = $exception->getMessage();
            $this->markAlreadyRun(false);
            $logger->addWarning("Exchange process interrupted with error, $error");
            $logger->write();
        }
    }
    /** **********************************************************************
     * mark exchange process already run
     *
     * @param   bool $condition             condition
     * @return  void
     ************************************************************************/
    private function markAlreadyRun(bool $condition) : void
    {
        $tempFile = new SplFileInfo($this->processTempFilePath);

        if ($condition)
        {
            $tempFile
                ->openFile('w')
                ->fwrite('');
        }
        else
        {
            @unlink($tempFile->getPathname());
        }
    }
    /** **********************************************************************
     * mark exchange process already run
     *
     * @return  bool                        condition
     ************************************************************************/
    private function checkAlreadyRun() : bool
    {
        $tempFile = new SplFileInfo($this->processTempFilePath);

        return $tempFile->isFile();
    }
}