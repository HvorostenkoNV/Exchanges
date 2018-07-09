<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field     as ParticipantField,
    Main\Exchange\Participants\Data\ItemData    as ParticipantItem,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantItemException,
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
class Matcher extends AbstractProcessor
{
    private static
        $idFieldType = 'item-id';
    private
        $participantsCollection         = [],
        $participantsFieldsCollection   = [],
        $matchingRulesCollection        = [],
        $collectedDataCollection        = [],
        $alreadyMatchedItemsCollection  = [],
        $matchedDataCollection          = [];
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

        $this->participantsCollection       = $this->getParticipantsCollection();
        $this->participantsFieldsCollection = $this->getParticipantsFieldsCollection();
        $this->matchingRulesCollection      = $this->getMatchingRulesCollection();

        try
        {
            $this->alreadyMatchedItemsCollection = $this->getAlreadyMatchedItemsCollection();
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("caught error on getting already matched items collection, \"$error\"", 'warning');
        }

        $this->collectedDataCollection  = $this->getCollectedDataCollection($collectedData);
        $this->matchedDataCollection    = $this->getMatchedDataCollection();

        foreach ($this->matchedDataCollection as $commonItem)
        {
            $matchedItem = new MatchedItem;

            foreach ($commonItem as $participantCode => $participantItemId)
            {
                try
                {
                    $participant        = $this->findParticipant($participantCode);
                    $participantItem    = $this->findParticipantItem($participantCode, $participantItemId);
                    $matchedItem->set($participant, $participantItem);
                }
                catch (UnknownParticipantException $exception)
                {
                    $this->addLogMessage("unknown participant \"$participantCode\" on constructing matched data item", 'warning');
                }
                catch (UnknownParticipantItemException $exception)
                {
                    $this->addLogMessage("not found participant item with \"$participantItemId\" in participant \"$participantCode\" on constructing matched data item", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $this->addLogMessage('unexpected error on constructing matched data item', 'warning');
                }
            }

            if ($matchedItem->count() == count($commonItem))
            {
                try
                {
                    $result->push($matchedItem);
                }
                catch (InvalidArgumentException $exception)
                {
                    $this->addLogMessage('unexpected error on constructing matched data item', 'warning');
                }
            }
        }

        if ($result->count() <= 0)
        {
            $this->addLogMessage('returning empty matched data while collected data is not empty', 'warning');
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
     * get data matching rules collection
     *
     * @return  array                               data matching rules collection
     * @example
     * [
     *      [
     *          participantCode => [participantFieldName, participantFieldName],
     *          participantCode => [participantFieldName, participantFieldName]
     *      ],
     *      [
     *          participantCode => [participantFieldName, participantFieldName],
     *          participantCode => [participantFieldName, participantFieldName]
     *      ]
     * ]
     ************************************************************************/
    private function getMatchingRulesCollection() : array
    {
        $result         = [];
        $matchingRules  = $this->getProcedure()->getDataMatchingRules();

        foreach ($matchingRules->getKeys() as $participantsSet)
        {
            $rule               = [];
            $ruleParticipants   = [];
            $procedureFieldsSet = $matchingRules->get($participantsSet);

            while ($participantsSet->valid())
            {
                try
                {
                    $participantCode = $participantsSet->current()->getCode();
                    $this->findParticipant($participantCode);
                    $ruleParticipants[] = $participantCode;
                }
                catch (UnknownParticipantException $exception)
                {
                    $participantCode = $exception->getParticipantCode();
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
                    try
                    {
                        $participantField       = $procedureField->current();
                        $participantCode        = $participantField->getParticipant()->getCode();
                        $participantFieldName   = $participantField->getField()->getParam('name');

                        $this->findParticipantField($participantCode, $participantFieldName);
                        if (in_array($participantCode, $ruleParticipants))
                        {
                            $procedureFieldArray[$participantCode] = $participantFieldName;
                        }
                    }
                    catch (UnknownParticipantFieldException $exception)
                    {
                        $participantCode = $exception->getParticipantCode();
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
                $result[] = $rule;
            }
            else
            {
                $this->addLogMessage("caught rule that includes less than two participants in data matching rules", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get collected data collection
     *
     * @param   CollectedData $collectedData        collected data
     * @return  array                               collected data collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantItemId   => participantItem,
     *          participantItemId   => participantItem
     *      ],
     *      participantCode =>
     *      [
     *          participantItemId   => participantItem,
     *          participantItemId   => participantItem
     *      ]
     * ]
     ************************************************************************/
    private function getCollectedDataCollection(CollectedData $collectedData) : array
    {
        $result = [];

        foreach ($collectedData->getKeys() as $participant)
        {
            try
            {
                $participantCode        = $participant->getCode();
                $participantDataArray   = [];
                $participant            = $this->findParticipant($participantCode);
                $participantData        = $collectedData->get($participant);
                $participantIdField     = $this->findParticipantIdField($participantCode);

                while ($participantData->count() > 0)
                {
                    $participantItem = $participantData->pop();

                    if ($participantItem->hasKey($participantIdField))
                    {
                        $participantItemId = $participantItem->get($participantIdField);
                        $participantDataArray[$participantItemId] = $participantItem;
                    }
                    else
                    {
                        $exception = new UnknownParticipantItemException;
                        $exception->setParticipantCode($participantCode);
                        throw $exception;
                    }
                }

                if (count($participantDataArray) > 0)
                {
                    $result[$participantCode] = $participantDataArray;
                }
            }
            catch (UnknownParticipantException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("unknown participant \"$participantCode\" in collected data", 'warning');
            }
            catch (UnknownParticipantFieldException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("participant \"$participantCode\" has no ID field", 'warning');
            }
            catch (UnknownParticipantItemException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("participant \"$participantCode\" has item with incorrect or absent ID", 'warning');
            }
            catch (RuntimeException $exception)
            {
                $this->addLogMessage("unexpected error on constructing collected data collection", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get already matched items collection
     *
     * @return  array                               already matched items collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantItemId   => commonItemId,
     *          participantItemId   => commonItemId
     *      ],
     *      participantCode =>
     *      [
     *          participantItemId   => commonItemId,
     *          participantItemId   => commonItemId
     *      ]
     * ]
     * @throws  RuntimeException                    getting collection problems
     ************************************************************************/
    private function getAlreadyMatchedItemsCollection() : array
    {
        try
        {
            $result         = [];
            $queryResult    = $this->queryMatchedItems();

            foreach ($queryResult as $item)
            {
                $participantCode    = $item['PARTICIPANT_CODE'];
                $participantItemId  = $item['PARTICIPANT_ITEM_ID'];
                $commonItemId       = $item['COMMON_ITEM_ID'];

                if (!array_key_exists($participantCode, $result))
                {
                    $result[$participantCode] = [];
                }

                $result[$participantCode][$participantItemId] = $commonItemId;
            }

            return $result;
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get matched data collection
     *
     * @return  array                               matched data
     * @example
     * [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     * ]
     ************************************************************************/
    private function getMatchedDataCollection() : array
    {
        $result         = [];
        $items          = [];
        $clearItemsData = function(array $items, array $matchedItems)
        {
            foreach ($matchedItems as $commonItemStructure)
            {
                foreach ($commonItemStructure as $participantCode => $participantItemId)
                {
                    $participantItemIdIndex = array_search($participantItemId, $items[$participantCode]);
                    unset($items[$participantCode][$participantItemIdIndex]);
                    if (count($items[$participantCode]) <= 0)
                    {
                        unset($items[$participantCode]);
                    }
                }
            }

            return $items;
        };

        foreach ($this->collectedDataCollection as $participantCode => $participantItemStructure)
        {
            $items[$participantCode] = array_keys($participantItemStructure);
        }

        try
        {
            $matchedItems   = $this->matchAlreadyMatchedItems($items);
            $result         = array_merge($result, $matchedItems);
            $items          = $clearItemsData($items, $matchedItems);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("problems with getting already matched items, \"$error\"", 'warning');
        }

        if (count($items) > 0)
        {
            $matchedItems   = $this->matchUnmatchedItems($items);
            $result         = array_merge($result, $matchedItems);
            $items          = $clearItemsData($items, $matchedItems);
        }

        foreach ($items as $participantCode => $participantItems)
        {
            foreach ($participantItems as $participantItemId)
            {
                $this->addLogMessage("unmatched item was found in participant \"$participantCode\" data with ID \"$participantItemId\"", 'warning');
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
     * find participant ID field
     *
     * @param   string $participantCode             participant code
     * @return  ParticipantField                    participant field
     * @throws  UnknownParticipantFieldException    participant has no ID field
     ************************************************************************/
    private function findParticipantIdField(string $participantCode) : ParticipantField
    {
        if (array_key_exists($participantCode, $this->participantsFieldsCollection))
        {
            foreach ($this->participantsFieldsCollection[$participantCode] as $fieldName => $field)
            {
                try
                {
                    $field = $this->findParticipantField($participantCode, $fieldName);
                    if ($field->getParam('type') == static::$idFieldType)
                    {
                        return $field;
                    }
                }
                catch (UnknownParticipantFieldException $exception)
                {
                    break;
                }
            }
        }

        $exception = new UnknownParticipantFieldException;
        $exception->setParticipantCode($participantCode);
        throw $exception;
    }
    /** **********************************************************************
     * find participant item
     *
     * @param   string  $participantCode            participant code
     * @param   mixed   $participantItemId          participant item ID
     * @return  ParticipantItem                     participant item
     * @throws  UnknownParticipantItemException     no participant item was found
     ************************************************************************/
    private function findParticipantItem(string $participantCode, $participantItemId) : ParticipantItem
    {
        if
        (
            array_key_exists($participantCode, $this->collectedDataCollection) &&
            array_key_exists($participantItemId, $this->collectedDataCollection[$participantCode])
        )
        {
            return $this->collectedDataCollection[$participantCode][$participantItemId];
        }

        $exception = new UnknownParticipantItemException;
        $exception->setParticipantCode($participantCode);
        $exception->setParticipantItemId($participantItemId);
        throw $exception;
    }
    /** **********************************************************************
     * match already matched items
     *
     * @param   array $items                        items
     * @example
     * [
     *      participantCode => [participantItemId, participantItemId, participantItemId],
     *      participantCode => [participantItemId, participantItemId, participantItemId]
     * ]
     * @return  array                               matched items
     * @example
     * [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     * ]
     * @throws  RuntimeException                    matching problems
     ************************************************************************/
    private function matchAlreadyMatchedItems(array $items) : array
    {
        try
        {
            $result         = [];
            $matchingMap    = $this->getMatchingMap();

            if (count($matchingMap) > 0)
            {
                foreach ($items as $participantCode => $participantItems)
                {
                    foreach ($participantItems as $participantItemId)
                    {
                        if (array_key_exists($participantCode, $matchingMap) && array_key_exists($participantItemId, $matchingMap[$participantCode]))
                        {
                            $commonItemId = $matchingMap[$participantCode][$participantItemId];

                            if (!array_key_exists($commonItemId, $result))
                            {
                                $result[$commonItemId] = [];
                            }

                            $result[$commonItemId][$participantCode] = $participantItemId;
                        }
                    }
                }
            }

            return array_values($result);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * match unmatched items
     *
     * @param   array $items                        items
     * @example
     * [
     *      participantCode => [participantItemId, participantItemId, participantItemId],
     *      participantCode => [participantItemId, participantItemId, participantItemId]
     * ]
     * @return  array                               matched items
     * @example
     * [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     * ]
     ************************************************************************/
    private function matchUnmatchedItems(array $items) : array
    {
        $result         = [];
        $matchingRules  = $this->matchingRulesCollection;
        $clearItemsData = function(array $items, array $matchedItems)
        {
            foreach ($matchedItems as $participantCode => $participantItemId)
            {
                if (array_key_exists($participantCode, $items))
                {
                    foreach ($items[$participantCode] as $participantFieldName => $participantFieldData)
                    {
                        if (array_key_exists($participantItemId, $participantFieldData))
                        {
                            unset($items[$participantCode][$participantFieldName][$participantItemId]);
                        }
                    }
                }
            }

            return $items;
        };

        if (count($matchingRules) <= 0)
        {
            $this->addLogMessage('unable to match unmatched items with empty data matching rules', 'notice');
            return $result;
        }

        $itemsData = $this->getConvertedItemsData($items);
        foreach ($matchingRules as $rule)
        {
            $data       = $this->getItemsGroupedByRule($itemsData, $rule);
            $ruleSize   = count($rule);

            foreach ($data as $group)
            {
                $matchedItems = [];

                foreach ($rule as $participantCode => $participantFields)
                {
                    if (array_key_exists($participantCode, $group))
                    {
                        $matchedItems[$participantCode] = $group[$participantCode][0];
                    }
                }

                if (count($matchedItems) == $ruleSize)
                {
                    $result[]   = $matchedItems;
                    $itemsData  = $clearItemsData($itemsData, $matchedItems);
                }
            }
        }

        $this->saveNewMatchedItems($result);
        return $result;
    }
    /** **********************************************************************
     * get participants items data as converted array
     *
     * @param   array $items                        items
     * @example
     * [
     *      participantCode => [participantItemId, participantItemId, participantItemId],
     *      participantCode => [participantItemId, participantItemId, participantItemId]
     * ]
     * @return  array                               data
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
    private function getConvertedItemsData(array $items) : array
    {
        $result = [];

        foreach ($items as $participantCode => $participantItems)
        {
            $result[$participantCode] = [];
            foreach ($participantItems as $participantItemId)
            {
                try
                {
                    $participantItem = $this->findParticipantItem($participantCode, $participantItemId);
                    foreach ($participantItem->getKeys() as $participantField)
                    {
                        $participantFieldName   = $participantField->getParam('name');
                        $value                  = $participantItem->get($participantField);

                        if (!array_key_exists($participantFieldName, $result[$participantCode]))
                        {
                            $result[$participantCode][$participantFieldName] = [];
                        }

                        $result[$participantCode][$participantFieldName][$participantItemId] = $value;
                    }
                }
                catch (UnknownParticipantItemException $exception)
                {

                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get items array grouped by rule
     *
     * @param   array   $data                       data
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
     * @param   array   $rule                       matching rule
     * @example
     * [
     *      participantCode => [participantFieldName, participantFieldName],
     *      participantCode => [participantFieldName, participantFieldName]
     * ]
     * @return  array                               combined converted data
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
    private function getItemsGroupedByRule(array $data, array $rule) : array
    {
        $result = [];

        foreach ($rule as $participantCode => $ruleParticipantFields)
        {
            $participantData        = [];
            $participantDataItemsId = [];

            if (!array_key_exists($participantCode, $data))
            {
                continue;
            }
            foreach ($ruleParticipantFields as $participantFieldName)
            {
                if (array_key_exists($participantFieldName, $data[$participantCode]))
                {
                    $participantData[$participantFieldName] = $data[$participantCode][$participantFieldName];
                }
            }
            if (count($participantData) != count($ruleParticipantFields))
            {
                continue;
            }

            foreach ($ruleParticipantFields as $participantFieldName)
            {
                $participantDataItemsId = array_merge($participantDataItemsId, array_keys($participantData[$participantFieldName]));
            }
            $participantDataItemsId = array_unique($participantDataItemsId);

            foreach ($participantDataItemsId as $participantItemId)
            {
                $itemValues = [];

                foreach ($ruleParticipantFields as $participantFieldName)
                {
                    $value = array_key_exists($participantItemId, $participantData[$participantFieldName])
                        ? $participantData[$participantFieldName][$participantItemId]
                        : null;
                    if (!$this->checkIsEmpty($value))
                    {
                        $itemValues[] = json_encode($value);
                    }
                }
                if (count($itemValues) != count($ruleParticipantFields))
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

        return array_values($result);
    }
    /** **********************************************************************
     * query matched items
     *
     * @return  array                               query result
     * @throws  RuntimeException                    querying error
     ************************************************************************/
    private function queryMatchedItems() : array
    {
        try
        {
            $result         = [];
            $db             = DB::getInstance();
            $procedureCode  = $this->getProcedure()->getCode();
            $queryString    = "
            SELECT
                matched_items_participants.`PROCEDURE_ITEM`       AS COMMON_ITEM_ID,
                participants.`CODE`                               AS PARTICIPANT_CODE,
                matched_items_participants.`PARTICIPANT_ITEM_ID`  AS PARTICIPANT_ITEM_ID
            FROM
                matched_items_participants
            INNER JOIN matched_items
                ON matched_items_participants.`PROCEDURE_ITEM` = matched_items.`ID`
            INNER JOIN procedures
                ON matched_items.`PROCEDURE` = procedures.`ID`
            INNER JOIN participants
                ON matched_items_participants.`PARTICIPANT` = participants.`ID`
            WHERE
                procedures.`CODE` = ?";

            $queryResult = $db->query($queryString, [$procedureCode]);
            while ($queryResult->count() > 0)
            {
                $item       = $queryResult->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $key)
                {
                    $itemArray[$key] = $item->get($key);
                }

                $result[] = $itemArray;
            }

            return $result;
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * save new matched items into DB
     *
     * @param   array $newMatchedItems              new matched items array
     * @example
     * [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     * ]
     ************************************************************************/
    private function saveNewMatchedItems(array $newMatchedItems) : void
    {
        try
        {
            $db                     = DB::getInstance();
            $procedureId            = $this->queryProcedureId();
            $participantsIdArray    = $this->queryParticipantsId();

            foreach ($newMatchedItems as $commonItemStructure)
            {
                $db->query("INSERT INTO matched_items (`PROCEDURE`) VALUES (?)", [$procedureId]);
                $commonItemId = $db->getLastInsertId();

                if ($commonItemId > 0)
                {
                    foreach ($commonItemStructure as $participantCode => $participantItemId)
                    {
                        if (!array_key_exists($participantCode, $participantsIdArray))
                        {
                            $this->addLogMessage("not found participant ID by code \"$participantCode\" on saving new matched items into DB", 'warning');
                            continue;
                        }

                        $participantId  = $participantsIdArray[$participantCode];
                        $queryString    = "INSERT INTO matched_items_participants (`PROCEDURE_ITEM`, `PARTICIPANT`, `PARTICIPANT_ITEM_ID`) VALUES (?, ?, ?)";

                        $db->query($queryString, [$commonItemId, $participantId, $participantItemId]);
                        $participantMatchedItemId = $db->getLastInsertId();

                        if ($participantMatchedItemId <= 0)
                        {
                            $this->addLogMessage("participant matched item \"$participantItemId\" was not written into DB with unknown problem", 'warning');
                        }
                    }
                }
                else
                {
                    $this->addLogMessage("common matched item was not written into DB with unknown problem", 'warning');
                }
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("problems with writing new matched items into DB, \"$error\"", 'warning');
        }
    }
    /** **********************************************************************
     * query procedure ID
     *
     * @return  int                                 procedure ID
     * @throws  RuntimeException                    querying error
     ************************************************************************/
    private function queryProcedureId() : int
    {
        try
        {
            $db             = DB::getInstance();
            $procedureCode  = $this->getProcedure()->getCode();
            $queryString    = "SELECT procedures.`ID` FROM procedures WHERE procedures.`CODE` = ?";
            $queryResult    = $db->query($queryString, [$procedureCode]);

            while ($queryResult->count() > 0)
            {
                $procedureId = (int) $queryResult->pop()->get('ID');
                if ($procedureId > 0)
                {
                    return $procedureId;
                }
            }

            throw new RuntimeException("procedure ID by code \"$procedureCode\" was not found");
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query participants ID
     *
     * @return  array                               participants ID
     * @example
     * [
     *      participantCode => participantId,
     *      participantCode => participantId
     * ]
     * @throws  RuntimeException                    querying error
     ************************************************************************/
    private function queryParticipantsId() : array
    {
        try
        {
            $result                     = [];
            $db                         = DB::getInstance();
            $participantsCodes          = array_keys($this->participantsCollection);
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participantsCodes)), ', ');
            $queryString                = "SELECT participants.`ID`, participants.`CODE` FROM participants WHERE participants.`CODE` IN ($participantsPlaceholder)";
            $queryResult                = $db->query($queryString, $participantsCodes);

            while ($queryResult->count() > 0)
            {
                $item               = $queryResult->pop();
                $participantId      = (int) $item->get('ID');
                $participantCode    = $item->get('CODE');

                $result[$participantCode] = $participantId;
            }

            return $result;
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * check value is empty
     *
     * @param   mixed $value                        value
     * @return  bool                                value is empty
     ************************************************************************/
    private function checkIsEmpty($value) : bool
    {
        switch (gettype($value))
        {
            case 'string':
                return strlen($value) > 0 ? false : true;
            case 'array':
                foreach ($value as $arrayValue)
                {
                    if (!$this->checkIsEmpty($arrayValue))
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
        $procedureCode  = $this->getProcedure()->getCode();
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