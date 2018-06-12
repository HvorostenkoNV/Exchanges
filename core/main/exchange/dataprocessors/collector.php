<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    InvalidArgumentException,
    ReflectionClass,
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
        $logger         = Logger::getInstance();
        $procedure      = $this->getProcedure();
        $procedureName  = $this->getObjectClassShortName($procedure);
        $participants   = $procedure->getParticipants();

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
                $logger->addWarning("Unexpected error on constructing collected data from procedure \"$procedureName\"");
            }

            $participants->next();
        }

        return $result;
    }
    /** **********************************************************************
     * get object class short name
     *
     * @param   object  $object                         object
     * @return  string                                  object class short name
     ************************************************************************/
    private function getObjectClassShortName($object) : string
    {
        $objectReflection = new ReflectionClass($object);

        return $objectReflection->getShortName();
    }
}