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
        $result             = new CollectedData;
        $logger             = Logger::getInstance();
        $procedure          = $this->getProcedure();
        $procedureClassName = get_class($procedure);
        $participants       = $procedure->getParticipants();

        $logger->addNotice("Collector working: start procedure \"$procedureClassName\" collecting data");
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
                $logger->addWarning("Collector working: unexpected error on constructing collected data item for procedure \"$procedureClassName\"");
            }

            $participants->next();
        }

        return $result;
    }
}