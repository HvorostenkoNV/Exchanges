<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    InvalidArgumentException,
    UnexpectedValueException,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field     as ParticipantField,
    Main\Exchange\Participants\Data\DataForDelivery,
    Main\Exchange\Participants\Data\ItemData    as ParticipantItemData,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Results\CombinedData,
    Main\Exchange\DataProcessors\Results\ProviderResult,
    Main\Exchange\DataProcessors\Data\CombinedItem;
/** ***********************************************************************************************
 * Matcher data-processor
 * match items off different participants
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Provider
{
    private static
        $participantIdFieldType = 'item-id';
    private
        $procedure                      = null,
        $procedureItemsMap              = null,
        $participantsCollection         = [],
        $participantsFieldsCollection   = [],
        $participantsIdFieldsCollection = [];
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure           $procedure  procedure
     * @param   ProcedureItemsMap   $map        procedure items map
     ************************************************************************/
    public function __construct(Procedure $procedure, ProcedureItemsMap $map)
    {
        $this->procedure            = $procedure;
        $this->procedureItemsMap    = $map;

        $this->fillParticipantsInfoCollections($procedure);
    }
    /** **********************************************************************
     * provide procedure participants data
     *
     * @param   CombinedData $combinedData  combined data
     * @return  ProviderResult              provider result
     ************************************************************************/
    public function provideData(CombinedData $combinedData) : ProviderResult
    {
        $result = new ProviderResult;

        $this->addLogMessage('start providing data', 'notice');
        if ($combinedData->count() <= 0)
        {
            $this->addLogMessage('caught empty combined data', 'notice');
            return $result;
        }

        $participantsSet = $this->procedure->getParticipants();
        while ($participantsSet->valid())
        {
            $participant        = $participantsSet->current();
            $participantCode    = $participant->getCode();
            $participantData    = new DataForDelivery;
            $participantIdField = null;

            try
            {
                $participantIdField = $this->findParticipantIdField($participantCode);
            }
            catch (UnknownParticipantFieldException $exception)
            {
                $this->addLogMessage("participant \"$participantCode\" has no ID field", 'warning');
                $participantsSet->next();
                continue;
            }

            foreach ($combinedData->getKeys() as $commonItemId)
            {
                $commonItemData         = $combinedData->get($commonItemId);
                $participantItemData    = $this->constructParticipantItemData($commonItemData, $participant);
                $participantItemId      = null;

                try
                {
                    $participantItemId = $this->procedureItemsMap->getItemId($participant, $commonItemId);
                }
                catch (UnexpectedValueException $exception)
                {
                    $participantItemId = 'new';
                }

                try
                {
                    $participantItemData->set($participantIdField, $participantItemId);
                    $participantData->push($participantItemData);
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing provided data, \"$error\"", 'warning');
                }
            }

            try
            {
                $deliveringResult = $participant->deliveryData($participantData);
                $result->set($participant, $deliveringResult);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing provided data, \"$error\"", 'warning');
            }

            $participantsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * fill participants info collections
     *
     * @param   Procedure $procedure                procedure
     ************************************************************************/
    private function fillParticipantsInfoCollections(Procedure $procedure) : void
    {
        $participantsSet = $procedure->getParticipants();

        if ($participantsSet->count() <= 0)
        {
            $this->addLogMessage('procedure has no participants', 'warning');
            return;
        }

        while ($participantsSet->valid())
        {
            $participant        = $participantsSet->current();
            $participantFields  = $participant->getFields();
            $participantCode    = $participant->getCode();

            $this->participantsCollection[$participantCode]         = $participant;
            $this->participantsFieldsCollection[$participantCode]   = [];

            while ($participantFields->valid())
            {
                $field      = $participantFields->current();
                $fieldName  = $field->getParam('name');

                $this->participantsFieldsCollection[$participantCode][$fieldName] = $field;
                if ($field->getParam('type') == self::$participantIdFieldType)
                {
                    $this->participantsIdFieldsCollection[$participantCode] = $field;
                }

                $participantFields->next();
            }

            if (count($this->participantsFieldsCollection[$participantCode]) <= 0)
            {
                $this->addLogMessage("participant \"$participantCode\" has no fields", 'warning');
            }
            if (!array_key_exists($participantCode, $this->participantsIdFieldsCollection))
            {
                $this->addLogMessage("participant \"$participantCode\" has no ID field", 'warning');
            }
            $participantsSet->next();
        }
    }
    /** **********************************************************************
     * find participant id field
     *
     * @param   string $participantCode             participant code
     * @return  ParticipantField                    participant id field
     * @throws  UnknownParticipantFieldException    participant id field not found
     ************************************************************************/
    private function findParticipantIdField(string $participantCode) : ParticipantField
    {
        if (array_key_exists($participantCode, $this->participantsIdFieldsCollection))
        {
            return $this->participantsIdFieldsCollection[$participantCode];
        }

        $exception = new UnknownParticipantFieldException;
        $exception->setParticipantCode($participantCode);
        throw $exception;
    }
    /** **********************************************************************
     * construct participant item data
     *
     * @param   CombinedItem    $combinedItemData   combined item data
     * @param   Participant     $participant        participant
     * @return  ParticipantItemData                 participant item data
     ************************************************************************/
    private function constructParticipantItemData(CombinedItem $combinedItemData, Participant $participant) : ParticipantItemData
    {
        $result             = new ParticipantItemData;
        $participantCode    = $participant->getCode();

        foreach ($combinedItemData->getKeys() as $procedureField)
        {
            $value = $combinedItemData->get($procedureField);

            $procedureField->rewind();
            while ($procedureField->valid())
            {
                $procedureParticipantField  = $procedureField->current();
                $fieldParticipantCode       = $procedureParticipantField->getParticipant()->getCode();
                $participantField           = $procedureParticipantField->getField();

                if ($fieldParticipantCode == $participantCode)
                {
                    try
                    {
                        $result->set($participantField, $value);
                    }
                    catch (InvalidArgumentException $exception)
                    {

                    }
                }

                $procedureField->next();
            }
        }

        return $result;
    }
    /** **********************************************************************
     * add message to log
     *
     * @param   string  $message                    message
     * @param   string  $type                       message type
     ************************************************************************/
    private function addLogMessage(string $message, string $type) : void
    {
        $logger         = Logger::getInstance();
        $procedureCode  = $this->procedure->getCode();
        $fullMessage    = "Provider for procedure \"$procedureCode\": $message";

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