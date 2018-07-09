<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    UnexpectedValueException,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field     as ParticipantField,
    Main\Exchange\Participants\Data\ItemData    as ParticipantItemData,
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
    private static
        $participantIdFieldType         = 'item-id';
    private
        $procedure                      = null,
        $procedureItemsMap              = null,
        $procedureData                  = null,
        $participantsCollection         = [],
        $participantsFieldsCollection   = [],
        $participantsIdFieldsCollection = [],
        $itemsDataCollection            = [],
        $matchingRulesCollection        = [],
        $matchedItemsCollection         = [];
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure           $procedure      procedure
     * @param   ProcedureItemsMap   $map            procedure items map
     * @param   ProcedureData       $data           procedure already exist data
     ************************************************************************/
    public function __construct(Procedure $procedure, ProcedureItemsMap $map, ProcedureData $data)
    {
        $this->procedure            = $procedure;
        $this->procedureItemsMap    = $map;
        $this->procedureData        = $data;

        $this->fillParticipantsInfoCollections($procedure);
        $this->fillMatchingRulesCollection($procedure);
        $this->fillDataCollectionFromProcedureData($procedure, $map, $data);
    }
    /** **********************************************************************
     * match procedure participants data
     *
     * @param   CollectedData $collectedData        collected data
     * @return  MatchedData                         matched data
     ************************************************************************/
    public function matchItems(CollectedData $collectedData) : MatchedData
    {
        $result = new MatchedData;

        $this->addLogMessage('start matching data', 'notice');
        if ($collectedData->count() <= 0)
        {
            $this->addLogMessage('caught empty collected data', 'notice');
            return $result;
        }

        $this->fillDataCollectionsFromCollectedData($collectedData);
        foreach ($collectedData->getKeys() as $participant)
        {
            $participantCode    = $participant->getCode();
            $participantData    = $collectedData->get($participant);

            try
            {
                $this->findParticipant($participantCode);
            }
            catch (UnknownParticipantException $exception)
            {
                $this->addLogMessage("unknown participant \"$participantCode\" on constructing matched data", 'warning');
                continue;
            }

            while ($participantData->count() > 0)
            {
                $participantItemData    = null;
                $commonItemId           = null;

                try
                {
                    $participantItemData    = $participantData->pop();
                    $commonItemId           = $this->findParticipantItemCommonId($participant, $participantItemData);
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

        if ($result->count() <= 0)
        {
            $this->addLogMessage('returning empty matched data while collected data is not empty', 'warning');
        }
echo"<br>===========MATCHER==========<br>";
$array = [];
foreach ($result->getKeys() as $commonItemId)
{
    $data = $result->get($commonItemId);
    $array[$commonItemId] = [];
    foreach ($data->getKeys() as $participant)
    {
        $array[$commonItemId][$participant->getCode()] = [];
        foreach ($data->get($participant)->getKeys() as $field)
        {
            $array[$commonItemId][$participant->getCode()][$field->getParam('name')] = $data->get($participant)->get($field);
        }
    }
}
echo"<pre>";
print_r($array);
echo"</pre>";
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
     * fill participants info collections
     *
     * @param   Procedure $procedure                procedure
     ************************************************************************/
    private function fillMatchingRulesCollection(Procedure $procedure) : void
    {
        $matchingRules = $procedure->getDataMatchingRules();

        if ($matchingRules->count() <= 0)
        {
            $this->addLogMessage('procedure has no data matching rules', 'warning');
            return;
        }

        foreach ($matchingRules->getKeys() as $participantsSet)
        {
            $rule               = [];
            $ruleParticipants   = [];
            $procedureFieldsSet = $matchingRules->get($participantsSet);

            while ($participantsSet->valid())
            {
                $participantCode = $participantsSet->current()->getCode();

                try
                {
                    $this->findParticipant($participantCode);
                    $ruleParticipants[] = $participantCode;
                }
                catch (UnknownParticipantException $exception)
                {
                    $this->addLogMessage("caught unknown participant \"$participantCode\" in data matching rules", 'warning');
                }

                $participantsSet->next();
            }
            while ($procedureFieldsSet->valid())
            {
                $procedureField         = $procedureFieldsSet->current();
                $procedureFieldArray    = [];

                $procedureField->rewind();
                while ($procedureField->valid())
                {
                    $participantField       = $procedureField->current();
                    $participantCode        = $participantField->getParticipant()->getCode();
                    $participantFieldName   = $participantField->getField()->getParam('name');

                    try
                    {
                        $this->findParticipantField($participantCode, $participantFieldName);
                        if (in_array($participantCode, $ruleParticipants))
                        {
                            $procedureFieldArray[$participantCode] = $participantFieldName;
                        }
                    }
                    catch (UnknownParticipantFieldException $exception)
                    {
                        $this->addLogMessage("caught unknown participant \"$participantCode\" in data matching rules", 'warning');
                    }

                    $procedureField->next();
                }

                if (count($procedureFieldArray) >= 2)
                {
                    foreach ($procedureFieldArray as $participantCode => $participantFieldName)
                    {
                        if (!array_key_exists($participantCode, $rule))
                        {
                            $rule[$participantCode] = [];
                        }
                        $rule[$participantCode][] = $participantFieldName;
                    }
                }
                else
                {
                    $this->addLogMessage('caught procedure field that includes less than two participant fields in data matching rules', 'warning');
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
                $this->matchingRulesCollection[] = $rule;
            }
            else
            {
                $this->addLogMessage("caught rule that includes less than two participants in data matching rules", 'warning');
            }
        }
    }
    /** **********************************************************************
     * fill participants info collections
     *
     * @param   Procedure           $procedure      procedure
     * @param   ProcedureItemsMap   $map            procedure items map
     * @param   ProcedureData       $data           procedure already exist data
     ************************************************************************/
    private function fillDataCollectionFromProcedureData(Procedure $procedure, ProcedureItemsMap $map, ProcedureData $data) : void
    {
        $procedureFieldsSet = $procedure->getFields();
        $dataItemsArray     = $data->getItemsIdArray();

        while ($procedureFieldsSet->valid())
        {
            $procedureField     = $procedureFieldsSet->current();
            $participantsFields = [];

            $procedureField->rewind();
            while ($procedureField->valid())
            {
                $participantField       = $procedureField->current();
                $participantCode        = $participantField->getParticipant()->getCode();
                $participantFieldName   = $participantField->getField()->getParam('name');

                $participantsFields[$participantCode] = $participantFieldName;
                $procedureField->next();
            }

            foreach ($dataItemsArray as $commonItemId)
            {
                $value = null;

                try
                {
                    $value = $data->getData($commonItemId, $procedureField);
                }
                catch (UnexpectedValueException $exception)
                {

                }

                foreach ($participantsFields as $participantCode => $participantFieldName)
                {
                    try
                    {
                        $participant        = $this->findParticipant($participantCode);
                        $participantItemId  = $map->getItemId($participant, $commonItemId);

                        if (!array_key_exists($participantCode, $this->itemsDataCollection))
                        {
                            $this->itemsDataCollection[$participantCode] = [];
                        }
                        if (!array_key_exists($participantFieldName, $this->itemsDataCollection[$participantCode]))
                        {
                            $this->itemsDataCollection[$participantCode][$participantFieldName] = [];
                        }
                        $this->itemsDataCollection[$participantCode][$participantFieldName][$participantItemId] = $value;
                    }
                    catch (UnknownParticipantException $exception)
                    {

                    }
                    catch (UnexpectedValueException $exception)
                    {

                    }
                }
            }

            $procedureFieldsSet->next();
        }
    }
    /** **********************************************************************
     * fill participants info collections
     *
     * @param   CollectedData $collectedData        collected data
     ************************************************************************/
    private function fillDataCollectionsFromCollectedData(CollectedData $collectedData) : void
    {
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
                $participantItemData = null;
                try
                {
                    $participantItemData = $participantData->pop();
                }
                catch (RuntimeException $exception)
                {
                    continue;
                }

                if ($participantItemData->hasKey($participantIdField))
                {
                    $participantItemId = $participantItemData->get($participantIdField);
                    foreach ($participantItemData->getKeys() as $participantField)
                    {
                        $value                  = $participantItemData->get($participantField);
                        $participantFieldName   = $participantField->getParam('name');

                        if (!array_key_exists($participantCode, $this->itemsDataCollection))
                        {
                            $this->itemsDataCollection[$participantCode] = [];
                        }
                        if (!array_key_exists($participantFieldName, $this->itemsDataCollection[$participantCode]))
                        {
                            $this->itemsDataCollection[$participantCode][$participantFieldName] = [];
                        }
                        $this->itemsDataCollection[$participantCode][$participantFieldName][$participantItemId] = $value;
                    }
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
     * find participant item common id
     *
     * @param   Participant         $participant    participant
     * @param   ParticipantItemData $data           participant item data
     * @return  int                                 participant item common id
     * @throws  UnexpectedValueException            participant item common id was not found
     ************************************************************************/
    private function findParticipantItemCommonId(Participant $participant, ParticipantItemData $data) : int
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
            $commonItemId       = $this->procedureItemsMap->getItemCommonId($participant, $participantItemId);
            return $commonItemId;
        }
        catch (UnexpectedValueException $exception)
        {

        }
        catch (UnknownParticipantFieldException $exception)
        {
            throw new UnexpectedValueException("participant \"$participantCode\" has no ID field");
        }
        catch (RuntimeException $exception)
        {
            throw new UnexpectedValueException("item of participant \"$participantCode\" has no ID");
        }

        $data = $this->getItemsMatchedByRules();
        foreach ($data as $matchedGroup)
        {
            if (array_key_exists($participantCode, $matchedGroup) && in_array($participantItemId, $matchedGroup[$participantCode]))
            {
                unset($matchedGroup[$participantCode]);
                foreach ($matchedGroup as $otherParticipantCode => $otherParticipantItems)
                {
                    try
                    {
                        $otherParticipant = $this->findParticipant($otherParticipantCode);
                        foreach ($otherParticipantItems as $otherParticipantItemId)
                        {
                            $commonItemId = $this->procedureItemsMap->getItemCommonId($otherParticipant, $otherParticipantItemId);
                            $this->procedureItemsMap->setParticipantItem($commonItemId, $participant, $participantItemId);
                            return $commonItemId;
                        }
                    }
                    catch (UnknownParticipantException $exception)
                    {

                    }
                    catch (UnexpectedValueException $exception)
                    {

                    }
                    catch (RuntimeException $exception)
                    {
                        throw new UnexpectedValueException($exception->getMessage());
                    }
                }
            }
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
     * get array of items matched by data matching rules
     *
     * @return  array                               array of items
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
    private function getItemsMatchedByRules() : array
    {
        if (count($this->matchedItemsCollection) > 0)
        {
            return $this->matchedItemsCollection;
        }

        $matchingRules  = $this->matchingRulesCollection;
        $itemsData      = $this->itemsDataCollection;

        foreach ($matchingRules as $rule)
        {
            foreach ($rule as $participantCode => $participantFields)
            {
                $participantData        = [];
                $participantDataItemsId = [];

                if (!array_key_exists($participantCode, $itemsData))
                {
                    continue;
                }
                foreach ($participantFields as $participantFieldName)
                {
                    if (array_key_exists($participantFieldName, $itemsData[$participantCode]))
                    {
                        $fieldData = $itemsData[$participantCode][$participantFieldName];
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
                    if (!array_key_exists($combinedData, $this->matchedItemsCollection))
                    {
                        $this->matchedItemsCollection[$combinedData] = [];
                    }
                    if (!array_key_exists($participantCode, $this->matchedItemsCollection[$combinedData]))
                    {
                        $this->matchedItemsCollection[$combinedData][$participantCode] = [];
                    }
                    $this->matchedItemsCollection[$combinedData][$participantCode][] = $participantItemId;
                }
            }
        }

        $this->matchedItemsCollection = array_values($this->matchedItemsCollection);
        return $this->matchedItemsCollection;
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