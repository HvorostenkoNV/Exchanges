<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    InvalidArgumentException,
    UnexpectedValueException,
    Main\Data\MapData,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet     as ParticipantFieldsSet,
    Main\Exchange\Participants\Data\ItemData        as ParticipantItemData,
    Main\Exchange\Participants\Data\DataForDelivery,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
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
    private
        $procedure                      = null,
        $procedureItemsMap              = null,
        $participantsCollection         = [],
        $participantsFieldsCollection   = [];
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure           $procedure  procedure
     * @param   ProcedureItemsMap   $map        procedure items map
     ************************************************************************/
    public function __construct(Procedure $procedure, ProcedureItemsMap $map)
    {
        $this->procedure                    = $procedure;
        $this->procedureItemsMap            = $map;
        $this->participantsCollection       = $procedure->getParticipants();
        $this->participantsFieldsCollection = new MapData;

        $this->participantsCollection->rewind();
        while ($this->participantsCollection->valid())
        {
            $participant            = $this->participantsCollection->current();
            $participantFieldsSet   = $participant->getFields();

            $this->participantsFieldsCollection->set($participant, $participantFieldsSet);
            $this->participantsCollection->next();
        }
    }
    /** **********************************************************************
     * provide procedure participants data
     *
     * @param   CombinedData $combinedData  combined data
     * @return  ProviderResult              provider result
     ************************************************************************/
    public function provideData(CombinedData $combinedData) : ProviderResult
    {
        $logger             = Logger::getInstance();
        $procedureCode      = $this->procedure->getCode();
        $logMessagePrefix   = "Provider for procedure \"$procedureCode\"";
        $result             = new ProviderResult;

        $logger->addNotice("$logMessagePrefix: start providing data");
        if ($combinedData->count() <= 0)
        {
            $logger->addNotice("$logMessagePrefix: caught empty combined data");
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
                $logger->addWarning("$logMessagePrefix: participant \"$participantCode\" has no ID field");
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
                    $logger->addWarning("$logMessagePrefix: unexpected error on constructing provided data, \"$error\"");
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
                $logger->addWarning("$logMessagePrefix: unexpected error on constructing provided data, \"$error\"");
            }

            $participantsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * find participant by code
     *
     * @param   string $participantCode             participant code
     * @return  Participant                         participant
     * @throws  UnknownParticipantException         participant not found
     ************************************************************************/
    private function findParticipant(string $participantCode) : Participant
    {
        $this->participantsCollection->rewind();
        while ($this->participantsCollection->valid())
        {
            $participant = $this->participantsCollection->current();
            if ($participant->getCode() == $participantCode)
            {
                return $participant;
            }
            $this->participantsCollection->next();
        }

        $exception = new UnknownParticipantException;
        $exception->setParticipantCode($participantCode);
        throw $exception;
    }
    /** **********************************************************************
     * find participant field
     *
     * @param   string $participantCode             participant code
     * @return  ParticipantFieldsSet                participant fields set
     * @throws  UnknownParticipantException         participant fields set not found
     ************************************************************************/
    private function findParticipantFieldsSet(string $participantCode) : ParticipantFieldsSet
    {
        try
        {
            $participant = $this->findParticipant($participantCode);
            return $this->participantsFieldsCollection->get($participant);
        }
        catch (UnknownParticipantException $exception)
        {
            $needException = new UnknownParticipantException;
            $needException->setParticipantCode($participantCode);
            throw $needException;
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
        $participantFieldsSet   = null;
        $needException          = new UnknownParticipantFieldException;
        $needException->setParticipantCode($participantCode);

        try
        {
            $participantFieldsSet = $this->findParticipantFieldsSet($participantCode);
        }
        catch (UnknownParticipantException $exception)
        {
            throw $needException;
        }

        $participantFieldsSet->rewind();
        while ($participantFieldsSet->valid())
        {
            $participantField = $participantFieldsSet->current();
            if ($participantField->getParam('type') == FieldsTypesManager::ID_FIELD_TYPE)
            {
                return $participantField;
            }
            $participantFieldsSet->next();
        }

        throw $needException;
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
            $value                  = $combinedItemData->get($procedureField);
            $participantFieldsSet   = $procedureField->getParticipantsFields();

            while ($participantFieldsSet->valid())
            {
                $participantField       = $participantFieldsSet->current();
                $fieldParticipantCode   = $participantField->getParticipant()->getCode();

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

                $participantFieldsSet->next();
            }
        }

        return $result;
    }
}