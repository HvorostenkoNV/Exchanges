<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    UnexpectedValueException,
    Main\Data\MapData,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet     as ParticipantFieldsSet,
    Main\Exchange\Participants\Data\ItemData        as ParticipantItemData,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Data\MatchedItem,
    Main\Exchange\DataProcessors\Results\CollectedData,
    Main\Exchange\DataProcessors\Results\MatchedData;
/** ***********************************************************************************************
 * Matcher data-processor
 * match items off different participants
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Matcher
{
    private
        $procedure                      = null,
        $procedureItemsMap              = null,
        $procedureData                  = null,
        $participantsCollection         = [],
        $participantsFieldsCollection   = [];
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure           $procedure      procedure
     * @param   ProcedureItemsMap   $map            procedure items map
     * @param   ProcedureData       $data           procedure already exist data
     ************************************************************************/
    public function __construct(Procedure $procedure, ProcedureItemsMap $map, ProcedureData $data)
    {
        $this->procedure                    = $procedure;
        $this->procedureItemsMap            = $map;
        $this->procedureData                = $data;
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
     * match procedure participants data
     *
     * @param   CollectedData $collectedData        collected data
     * @return  MatchedData                         matched data
     ************************************************************************/
    public function matchItems(CollectedData $collectedData) : MatchedData
    {
        $this->addLogMessage('start matching data', 'notice');

        $result             = new MatchedData;
        $collectedDataEmpty = true;
        $matchedData        = $this->getMatchedItems($collectedData);

        foreach ($collectedData->getKeys() as $participant)
        {
            $participantCode    = $participant->getCode();
            $participantData    = $collectedData->get($participant);

            if ($participantData->count() > 0)
            {
                $collectedDataEmpty = false;
            }

            try
            {
                $this->findParticipant($participantCode);
            }
            catch (UnknownParticipantException $exception)
            {
                $this->addLogMessage("unknown participant \"$participantCode\" on constructing matched data", 'warning');
                continue;
            }

            while (!$participantData->isEmpty())
            {
                $participantItemData    = null;
                $commonItemId           = null;

                try
                {
                    $participantItemData    = $participantData->pop();
                    $commonItemId           = $this->findParticipantItemCommonId($participant, $participantItemData, $matchedData);
                }
                catch (UnexpectedValueException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("caught item in participant \"$participantCode\" without common ID on constructing matched data, \"$error\"", 'warning');
                    continue;
                }
                catch (RuntimeException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing matched data, \"$error\"", 'warning');
                    continue;
                }

                try
                {
                    if (!$result->hasKey($commonItemId))
                    {
                        $result->set($commonItemId, new MatchedItem);
                    }
                    $result->get($commonItemId)->set($participant, $participantItemData);
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing matched data, \"$error\"", 'warning');
                }
            }
        }

        if ($collectedDataEmpty)
        {
            $this->addLogMessage('caught empty collected data', 'notice');
        }
        elseif ($result->count() <= 0)
        {
            $this->addLogMessage('returning empty matched data while collected data is not empty', 'warning');
        }

        return $result;
    }
    /** **********************************************************************
     * get array of items matched by data matching rules
     *
     * @param   CollectedData $collectedData        collected data
     * @return  array                               matched items
     * @example
     * [
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ],
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ]
     * ]
     ************************************************************************/
    private function getMatchedItems(CollectedData $collectedData) : array
    {
        $procedureData  = $this->getProcedureDataAsArray();
        $collectedData  = $this->getCollectedDataAsArray($collectedData);
        $fullData       = $procedureData;

        foreach ($collectedData as $participantCode => $participantData)
        {
            if (!array_key_exists($participantCode, $fullData))
            {
                $fullData[$participantCode] = [];
            }
            foreach ($participantData as $participantFieldName => $participantFieldData)
            {
                if (!array_key_exists($participantFieldName, $fullData[$participantCode]))
                {
                    $fullData[$participantCode][$participantFieldName] = [];
                }
                foreach ($participantFieldData as $participantItemId => $value)
                {
                    $fullData[$participantCode][$participantFieldName][$participantItemId] = $value;
                }
            }
        }

        return $this->getItemsMatchedByMatchingRules($fullData);
    }
    /** **********************************************************************
     * get procedure data as array
     *
     * @return  array                               procedure data as array
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ]
     * ]
     ************************************************************************/
    private function getProcedureDataAsArray() : array
    {
        $result             = [];
        $procedureFieldsSet = $this->procedure->getFields();
        $dataItemsArray     = $this->procedureData->getItemsIdArray();

        $procedureFieldsSet->rewind();
        while ($procedureFieldsSet->valid())
        {
            $procedureField = $procedureFieldsSet->current();

            foreach ($dataItemsArray as $commonItemId)
            {
                $value                  = null;
                $participantFieldsSet   = $procedureField->getParticipantsFields();

                try
                {
                    $value = $this->procedureData->getData($commonItemId, $procedureField);
                }
                catch (UnexpectedValueException $exception)
                {

                }

                while ($participantFieldsSet->valid())
                {
                    try
                    {
                        $participantField       = $participantFieldsSet->current();
                        $participantFieldName   = $participantField->getParam('name');
                        $participant            = $participantField->getParticipant();
                        $participantCode        = $participant->getCode();
                        $participantItemId      = $this->procedureItemsMap->getItemId($participant, $commonItemId);

                        if (!array_key_exists($participantCode, $result))
                        {
                            $result[$participantCode] = [];
                        }
                        if (!array_key_exists($participantFieldName, $result[$participantCode]))
                        {
                            $result[$participantCode][$participantFieldName] = [];
                        }
                        $result[$participantCode][$participantFieldName][$participantItemId] = $value;
                    }
                    catch (UnexpectedValueException $exception)
                    {

                    }

                    $participantFieldsSet->next();
                }
            }

            $procedureFieldsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * get collected participants data as array
     *
     * @param   CollectedData $collectedData        collected data
     * @return  array                               procedure data as array
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ]
     * ]
     ************************************************************************/
    private function getCollectedDataAsArray(CollectedData $collectedData) : array
    {
        $result = [];

        foreach ($collectedData->getKeys() as $participant)
        {
            $participantCode        = $participant->getCode();
            $participantData        = $collectedData->get($participant);
            $participantIdField     = null;
            $participantDataCount   = $participantData->count();

            try
            {
                $participantIdField = $this->findParticipantIdField($participantCode);
            }
            catch (UnknownParticipantFieldException $exception)
            {
                continue;
            }

            for ($index = $participantDataCount; $index > 0; $index--)
            {
                $participantItemData    = null;
                $participantItemId      = null;

                try
                {
                    $participantItemData    = $participantData->pop();
                    $participantItemId      = (string) $participantItemData->get($participantIdField);
                }
                catch (RuntimeException $exception)
                {
                    continue;
                }

                foreach ($participantItemData->getKeys() as $participantField)
                {
                    $value                  = $participantItemData->get($participantField);
                    $participantFieldName   = $participantField->getParam('name');

                    if (!array_key_exists($participantCode, $result))
                    {
                        $result[$participantCode] = [];
                    }
                    if (!array_key_exists($participantFieldName, $result[$participantCode]))
                    {
                        $result[$participantCode][$participantFieldName] = [];
                    }

                    $result[$participantCode][$participantFieldName][$participantItemId] = $value;
                }

                try
                {
                    $participantData->push($participantItemData);
                }
                catch (InvalidArgumentException $exception)
                {

                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get array of items matched by data matching rules
     *
     * @param   array $data                         data
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ],
     *          participantFieldName    =>
     *          [
     *              participantItemId   => value,
     *              participantItemId   => value
     *          ]
     *      ]
     * ]
     * @return  array                               matched items
     * @example
     * [
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ],
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ]
     * ]
     ************************************************************************/
    private function getItemsMatchedByMatchingRules(array $data) : array
    {
        $result         = [];
        $matchingRules  = $this->getMatchingRulesAsArray();

        foreach ($matchingRules as $rule)
        {
            foreach ($rule as $participantCode => $participantFields)
            {
                $participantData        = [];
                $participantDataItemsId = [];

                if (!array_key_exists($participantCode, $data))
                {
                    continue;
                }
                foreach ($participantFields as $participantFieldName)
                {
                    if (array_key_exists($participantFieldName, $data[$participantCode]))
                    {
                        $fieldData = $data[$participantCode][$participantFieldName];
                        $participantData[$participantFieldName] = $fieldData;
                        $participantDataItemsId = array_merge($participantDataItemsId, array_keys($fieldData));
                    }
                }
                if (count($participantData) != count($participantFields))
                {
                    continue;
                }

                foreach (array_unique($participantDataItemsId) as $participantItemId)
                {
                    $itemValues = [];

                    foreach ($participantFields as $participantFieldName)
                    {
                        $value = array_key_exists($participantItemId, $participantData[$participantFieldName])
                            ? $participantData[$participantFieldName][$participantItemId]
                            : null;
                        if (!$this->checkValueIsEmpty($value))
                        {
                            $itemValues[] = json_encode($value);
                        }
                    }
                    if (count($itemValues) != count($participantFields))
                    {
                        continue;
                    }

                    $combinedData = implode('|', $itemValues);
                    if (!array_key_exists($combinedData, $result))
                    {
                        $result[$combinedData] = [];
                    }
                    if (!array_key_exists($participantCode, $result[$combinedData]))
                    {
                        $result[$combinedData][$participantCode] = [];
                    }
                    $result[$combinedData][$participantCode][] = $participantItemId;
                }
            }
        }

        return array_values($result);
    }
    /** **********************************************************************
     * get matching rules as array
     *
     * @return  array                               matching rules as array
     ************************************************************************/
    private function getMatchingRulesAsArray() : array
    {
        $result         = [];
        $matchingRules  = $this->procedure->getDataMatchingRules();

        foreach ($matchingRules->getKeys() as $participantsSet)
        {
            $rule               = [];
            $procedureFieldsSet = $matchingRules->get($participantsSet);

            $participantsSet->rewind();
            while ($participantsSet->valid())
            {
                $participantCode = $participantsSet->current()->getCode();
                $rule[$participantCode] = [];
                $participantsSet->next();
            }

            $procedureFieldsSet->rewind();
            while ($procedureFieldsSet->valid())
            {
                $procedureField         = $procedureFieldsSet->current();
                $participantFieldsSet   = $procedureField->getParticipantsFields();

                while ($participantFieldsSet->valid())
                {
                    $participantField       = $participantFieldsSet->current();
                    $participantCode        = $participantField->getParticipant()->getCode();
                    $participantFieldName   = $participantField->getParam('name');

                    if (array_key_exists($participantCode, $rule))
                    {
                        $rule[$participantCode][] = $participantFieldName;
                    }

                    $participantFieldsSet->next();
                }

                $procedureFieldsSet->next();
            }

            $participantsFieldsCouplesSize = [];
            foreach ($rule as $participantFields)
            {
                $participantsFieldsCouplesSize[] = count($participantFields);
            }
            $maxCouplesSize = count($participantsFieldsCouplesSize) > 0 ? max($participantsFieldsCouplesSize) : 0;

            foreach ($rule as $participantCode => $participantFields)
            {
                if (count($participantFields) != $maxCouplesSize)
                {
                    unset($rule[$participantCode]);
                }
            }

            if (count($rule) >= 2)
            {
                $result[] = $rule;
            }
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
     * find participant item common id
     *
     * @param   Participant         $participant    participant
     * @param   ParticipantItemData $data           participant item data
     * @param   array               $matchedData    matched data
     * @example
     * [
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ],
     *      [
     *          participantCode => [participantItemId, participantItemId],
     *          participantCode => [participantItemId, participantItemId]
     *      ]
     * ]
     * @return  int                                 participant item common id
     * @throws  UnexpectedValueException            participant item common id was not found
     ************************************************************************/
    private function findParticipantItemCommonId(Participant $participant, ParticipantItemData $data, array $matchedData) : int
    {
        $participantItemId  = null;
        $participantCode    = $participant->getCode();

        try
        {
            $participantIdField = $this->findParticipantIdField($participantCode);
            if (!$data->hasKey($participantIdField))
            {
                throw new RuntimeException;
            }
            $participantItemId  = (string) $data->get($participantIdField);
        }
        catch (UnknownParticipantFieldException $exception)
        {
            throw new UnexpectedValueException("participant \"$participantCode\" has no ID field");
        }
        catch (RuntimeException $exception)
        {
            throw new UnexpectedValueException("item of participant \"$participantCode\" has no ID");
        }

        foreach ($matchedData as $matchedGroup)
        {
            if (array_key_exists($participantCode, $matchedGroup) && in_array($participantItemId, $matchedGroup[$participantCode]))
            {
                unset($matchedGroup[$participantCode]);
                foreach ($matchedGroup as $otherParticipantCode => $otherParticipantItems)
                {
                    $otherParticipant = null;

                    try
                    {
                        $otherParticipant = $this->findParticipant($otherParticipantCode);
                    }
                    catch (UnknownParticipantException $exception)
                    {
                        continue;
                    }

                    foreach ($otherParticipantItems as $otherParticipantItemId)
                    {
                        try
                        {
                            $commonItemId = $this->procedureItemsMap->getItemCommonId($otherParticipant, (string) $otherParticipantItemId);
                            $this->procedureItemsMap->setParticipantItem($commonItemId, $participant, $participantItemId);
                            return $commonItemId;
                        }
                        catch (UnexpectedValueException $exception)
                        {
                            continue;
                        }
                        catch (RuntimeException $exception)
                        {
                            throw new UnexpectedValueException($exception->getMessage());
                        }
                    }
                }
            }
        }

        try
        {
            return $this->procedureItemsMap->getItemCommonId($participant, $participantItemId);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        try
        {
            $commonItemId = $this->procedureItemsMap->createNewItem($participant, $participantItemId);
            return $commonItemId;
        }
        catch (RuntimeException $exception)
        {
            throw new UnexpectedValueException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * check value is empty
     *
     * @param   mixed $value                        value
     * @return  bool                                value is empty
     ************************************************************************/
    private function checkValueIsEmpty($value) : bool
    {
        switch (gettype($value))
        {
            case 'string':
                return strlen($value) > 0 ? false : true;
            case 'array':
                foreach ($value as $arrayValue)
                {
                    if (!$this->checkValueIsEmpty($arrayValue))
                    {
                        return false;
                    }
                }

                return true;
            case 'NULL':
                return true;
            default:
                return false;
        }
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
        $fullMessage    = "Matcher for procedure \"$procedureCode\": $message";

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