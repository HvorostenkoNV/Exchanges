<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    InvalidArgumentException,
    Main\Helpers\Logger,
    Main\Exchange\DataProcessors\Results\CollectedData;
/** ***********************************************************************************************
 * Collector data-processor
 * collects procedure participants provided data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Collector extends AbstractProcessor
{
    /** **********************************************************************
     * collect procedure participants data
     *
     * @return  CollectedData               collected data
     ************************************************************************/
    public function collectData() : CollectedData
    {
        $result         = new CollectedData;
        $participants   = $this->getProcedure()->getParticipants();

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
                $this->addLogMessage('unexpected error on constructing collected data item', 'warning');
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
        $procedureCode  = $this->getProcedure()->getCode();
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