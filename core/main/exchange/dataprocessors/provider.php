<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field as ParticipantField,
    Main\Exchange\Participants\Data\DataForDelivery,
    Main\Exchange\Participants\Data\ItemData as ParticipantItemData,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\DataProcessors\Results\CombinedData,
    Main\Exchange\DataProcessors\Results\ProviderResult;
/** ***********************************************************************************************
 * Matcher data-processor
 * match items off different participants
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Provider extends AbstractProcessor
{
    private
        $participantsCollection         = [],
        $participantsFieldsCollection   = [],
        $participantsDataCollection     = [];
    /** **********************************************************************
     * provide procedure participants data
     *
     * @param   CombinedData $combinedData          combined data
     * @return  ProviderResult                      provider result
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

        $this->participantsCollection       = $this->getParticipantsCollection();
        $this->participantsFieldsCollection = $this->getParticipantsFieldsCollection();
        $this->participantsDataCollection   = $this->getParticipantsDataCollection($combinedData);

        foreach ($this->participantsDataCollection as $participantCode => $participantDataArray)
        {
            $participantData = new DataForDelivery;

            foreach ($participantDataArray as $participantItemDataArray)
            {
                $participantItemData = new ParticipantItemData;

                foreach ($participantItemDataArray as $participantFieldName => $value)
                {
                    try
                    {
                        $participantField = $this->findParticipantField($participantCode, $participantFieldName);
                        $participantItemData->set($participantField, $value);
                    }
                    catch (UnknownParticipantFieldException $exception)
                    {
                        $this->addLogMessage("unknown participant field \"$participantFieldName\" in participant \"$participantCode\" on constructing participant item data", 'warning');
                    }
                    catch (InvalidArgumentException $exception)
                    {
                        $error = $exception->getMessage();
                        $this->addLogMessage("error on constructing participant item data, \"$error\"", 'warning');
                    }
                }

                try
                {
                    $participantData->push($participantItemData);
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("error on constructing participant data for delivery, \"$error\"", 'warning');
                }
            }

            try
            {
                $participant        = $this->findParticipant($participantCode);
                $deliveryResult     = $participant->deliveryData($participantData);
                $result->set($participant, $deliveryResult);
            }
            catch (UnknownParticipantException $exception)
            {
                $this->addLogMessage("unknown participant \"$participantCode\" on constructing provided data item", 'warning');
            }
            catch (InvalidArgumentException $exception)
            {
                $this->addLogMessage('unexpected error on constructing provided data item', 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participants collection
     *
     * @return  array                               participants collection
     * @example
     * [
     *      participantCode => participant,
     *      participantCode => participant
     * ]
     ************************************************************************/
    private function getParticipantsCollection() : array
    {
        $result             = [];
        $participantsSet    = $this->getProcedure()->getParticipants();

        while ($participantsSet->valid())
        {
            $participant = $participantsSet->current();

            $result[$participant->getCode()] = $participant;
            $participantsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * get participants fields collection
     *
     * @return  array                               participants fields collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    => participantField,
     *          participantFieldName    => participantField
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    => participantField,
     *          participantFieldName    => participantField
     *      ]
     * ]
     ************************************************************************/
    private function getParticipantsFieldsCollection() : array
    {
        $result = [];

        foreach ($this->participantsCollection as $participantCode => $participant)
        {
            try
            {
                $participant        = $this->findParticipant($participantCode);
                $participantFields  = $participant->getFields();

                $result[$participantCode] = [];
                while ($participantFields->valid())
                {
                    $participantField       = $participantFields->current();
                    $participantFieldName   = $participantField->getParam('name');

                    $result[$participantCode][$participantFieldName] = $participantField;
                    $participantFields->next();
                }
            }
            catch (UnknownParticipantException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participants data collection
     *
     * @param   CombinedData $combinedData          combined data
     * @return  array                               participants data collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ],
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ]
     *      ],
     *      participantCode =>
     *      [
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ],
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ]
     *      ]
     * ]
     ************************************************************************/
    private function getParticipantsDataCollection(CombinedData $combinedData) : array
    {
        $result = [];

        foreach ($this->participantsCollection as $participantCode => $participant)
        {
            $result[$participantCode] = [];
        }

        while ($combinedData->count() > 0)
        {
            try
            {
                $item       = $combinedData->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $procedureField)
                {
                    $value = $item->get($procedureField);

                    $procedureField->rewind();
                    while ($procedureField->valid())
                    {
                        $participantField       = $procedureField->current();
                        $participantCode        = $participantField->getParticipant()->getCode();
                        $participantFieldName   = $participantField->getField()->getParam('name');

                        if (!array_key_exists($participantCode, $itemArray))
                        {
                            $itemArray[$participantCode] = [];
                        }
                        $itemArray[$participantCode][$participantFieldName] = $value;
                        $procedureField->next();
                    }
                }

                foreach ($itemArray as $participantCode => $participantItemData)
                {
                    try
                    {
                        $this->findParticipant($participantCode);
                        $result[$participantCode][] = $participantItemData;
                    }
                    catch (UnknownParticipantException $exception)
                    {
                        $this->addLogMessage("unknown participant \"$participantCode\" on constructing participants collection", 'warning');
                    }
                }
            }
            catch (RuntimeException $exception)
            {
                $this->addLogMessage('unexpected error on constructing participants data collection', 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * find participant by code
     *
     * @param   string  $participantCode            participant code
     * @return  Participant                         participant
     * @throws  UnknownParticipantException         participant not found
     ************************************************************************/
    private function findParticipant(string $participantCode) : Participant
    {
        if (array_key_exists($participantCode, $this->participantsCollection))
        {
            return $this->participantsCollection[$participantCode];
        }

        $exception = new UnknownParticipantException;
        $exception->setParticipantCode($participantCode);
        throw $exception;
    }
    /** **********************************************************************
     * find participant field
     *
     * @param   string  $participantCode            participant code
     * @param   string  $fieldName                  participant field name
     * @return  ParticipantField                    participant field
     * @throws  UnknownParticipantFieldException    participant has no such field
     ************************************************************************/
    private function findParticipantField(string $participantCode, string $fieldName) : ParticipantField
    {
        if
        (
            array_key_exists($participantCode, $this->participantsFieldsCollection) &&
            array_key_exists($fieldName, $this->participantsFieldsCollection[$participantCode])
        )
        {
            return $this->participantsFieldsCollection[$participantCode][$fieldName];
        }

        $exception = new UnknownParticipantFieldException;
        $exception->setParticipantCode($participantCode);
        $exception->setParticipantFieldName($fieldName);
        throw $exception;
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
        $procedureCode  = $this->getProcedure()->getCode();
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