<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    InvalidArgumentException,
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Results\CollectedData;
/** ***********************************************************************************************
 * Collector data-processor
 * collects procedure participants provided data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Collector
{
    private $procedure = null;
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure $procedure        procedure
     ************************************************************************/
    public function __construct(Procedure $procedure)
    {
        $this->procedure = $procedure;
    }
    /** **********************************************************************
     * collect procedure participants data
     *
     * @return  CollectedData               collected data
     ************************************************************************/
    public function collectData() : CollectedData
    {
        $logger             = Logger::getInstance();
        $procedureCode      = $this->procedure->getCode();
        $logMessagePrefix   = "Collector for procedure \"$procedureCode\"";
        $participants       = $this->procedure->getParticipants();
        $result             = new CollectedData;

        $logger->addNotice("$logMessagePrefix: collecting data start");
        while ($participants->valid())
        {
            $participant        = $participants->current();
            $participantData    = $participant->getProvidedData();

            try
            {
                $result->set($participant, $participantData);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("$logMessagePrefix: unexpected error on constructing collected data item, \"$error\"");
            }

            $participants->next();
        }

        return $result;
    }
}