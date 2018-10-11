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
        $result         = new CollectedData;
        $participants   = $this->procedure->getParticipants();

        $this->addLogMessage('collecting data start', 'notice');
        while ($participants->valid())
        {
            try
            {
                $participant        = $participants->current();
                $participantData    = $participant->getProvidedData();
                $result->set($participant, $participantData);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing collected data item, \"$error\"", 'warning');
            }

            $participants->next();
        }

        return $result;
    }
    /** **********************************************************************
     * add message to log
     *
     * @param   string  $message            message
     * @param   string  $type               message type
     ************************************************************************/
    private function addLogMessage(string $message, string $type) : void
    {
        $logger         = Logger::getInstance();
        $procedureCode  = $this->procedure->getCode();
        $fullMessage    = "Collector for procedure \"$procedureCode\": $message";

        switch ($type)
        {
            case 'warning':
                $logger->addWarning($fullMessage);
                break;
            case 'notice':
            default:
                $logger->addNotice($fullMessage);
                break;
        }
    }
}