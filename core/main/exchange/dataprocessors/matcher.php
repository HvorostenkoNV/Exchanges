<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    LogicException,
    RuntimeException,
    UnexpectedValueException,
    InvalidArgumentException,
    ReflectionException,
    ReflectionClass,
    Main\Helpers\Logger,
    Main\Helpers\DB,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field     as ParticipantField,
    Main\Exchange\Participants\Data\ItemData    as ParticipantItem,
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
    private static $idFieldType = 'item-id';
    /** **********************************************************************
     * match procedure participants data
     *
     * @param   CollectedData $collectedData            collected data
     * @return  MatchedData                             matcher result
     ************************************************************************/
    public function matchItems(CollectedData $collectedData) : MatchedData
    {
        $result     = new MatchedData;
        $procedure  = $this->getProcedure();

        $this->addLogMessage('start matching data', 'notice');
        if ($collectedData->count() <= 0)
        {
            $this->addLogMessage('caught empty collected data', 'notice');
            return $result;
        }

        $participantsMap    = $this->getParticipantsMap($procedure);
        $matchedData        = $this->constructMatchedData($procedure, $collectedData);

        foreach ($matchedData as $commonItem)
        {
            try
            {
                $matchedItem = new MatchedItem;

                foreach ($commonItem as $participantCode => $participantItem)
                {
                    $participant = $participantsMap[$participantCode];
                    $matchedItem->set($participant, $participantItem);
                }

                $result->push($matchedItem);
            }
            catch (InvalidArgumentException $exception)
            {
                $this->addLogMessage('unexpected error on constructing matched data item', 'warning');
            }
        }

        if ($result->count() <= 0)
        {
            $this->addLogMessage('returning empty matched data', 'notice');
        }

        return $result;
    }
    /** **********************************************************************
     * construct procedure participants matched data array
     *
     * @param   Procedure       $procedure              procedure
     * @param   CollectedData   $collectedData          collected data
     * @return  array                                   matched data array
     * @example
     * [
     *      [
     *          participantCode => participantItem,
     *          participantCode => participantItem
     *      ],
     *      [
     *          participantCode => participantItem,
     *          participantCode => participantItem
     *      ]
     * ]
     ************************************************************************/
    private function constructMatchedData(Procedure $procedure, CollectedData $collectedData) : array
    {
        $result                 = [];
        $convertedCollectedData = $this->convertCollectedData($collectedData);

        try
        {
            $matchingMap            = $this->getMatchingMap($procedure);
            $alreadyMatchedItems    = $this->matchAlreadyMatchedItems($convertedCollectedData, $matchingMap);
            $this->fillMatchedData($result, $convertedCollectedData, $alreadyMatchedItems);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("problems with getting already matched items \"$error\"", 'warning');
        }

        if (count($convertedCollectedData) > 0)
        {
            $matchingRules      = $procedure->getDataMatchingRules();
            $newMatchedItems    = $this->matchUnmatchedItems($convertedCollectedData, $matchingRules);

            $this->fillMatchedData($result, $convertedCollectedData, $newMatchedItems);
            $this->saveNewMatchedItems($newMatchedItems);
        }

        foreach ($convertedCollectedData as $participantCode => $participantItemInfo)
        {
            foreach ($participantItemInfo as $participantItemId => $participantItem)
            {
                $this->addLogMessage("unmatched item was found form participant \"$participantCode\" with \"$participantItemId\" ID", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * convert collected data to array
     *
     * @param   CollectedData   $collectedData          collected data
     * @return  array                                   collected data as array
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
    private function convertCollectedData(CollectedData $collectedData) : array
    {
        $result = [];

        foreach ($collectedData->getKeys() as $participant)
        {
            $participantCode        = $this->getObjectShortName($participant);
            $participantDataArray   = [];

            try
            {
                $participantData    = $collectedData->get($participant);
                $participantIdField = $this->findParticipantIdField($participant);

                while ($participantData->count() > 0)
                {
                    $participantItem    = $participantData->pop();
                    $participantItemId  = $this->findParticipantItemId($participantItem, $participantIdField);
                    $participantDataArray[$participantItemId] = $participantItem;
                }
            }
            catch (LogicException $exception)
            {
                $this->addLogMessage("participant \"$participantCode\" has no ID field", 'warning');
            }
            catch (UnexpectedValueException $exception)
            {
                $this->addLogMessage("participant \"$participantCode\" has item with incorrect or absent ID", 'warning');
            }
            catch (RuntimeException $exception)
            {
                $this->addLogMessage("unexpected error on converted collected data form participant \"$participantCode\" to workable array", 'warning');
            }

            if (count($participantDataArray) > 0)
            {
                $result[$participantCode] = $participantDataArray;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get procedure items matching map
     *
     * @param   Procedure $procedure                    procedure
     * @return  array                                   procedure items matching map
     * @throws  RuntimeException                        getting matching map problems
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
     ************************************************************************/
    private function getMatchingMap(Procedure $procedure) : array
    {
        try
        {
            $result         = [];
            $queryResult    = $this->queryMatchedItems($procedure);

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
     * match already matched items
     *
     * @param   array   $collectedData                  converted collected data
     * @param   array   $matchingMap                    procedure items matching map
     * @return  array                                   already matched items array
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
    private function matchAlreadyMatchedItems(array $collectedData, array $matchingMap) : array
    {
        $result = [];

        if (count($matchingMap) <= 0)
        {
            return $result;
        }

        foreach ($collectedData as $participantCode => $participantItemInfo)
        {
            foreach ($participantItemInfo as $participantItemId => $participantItem)
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

        return array_values($result);
    }
    /** **********************************************************************
     * match unmatched items
     *
     * @param   array               $collectedData      converted collected data
     * @param   DataMatchingRules   $matchingRules      procedure data matching rules
     * @return  array                                   new matched items array
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
    private function matchUnmatchedItems(array $collectedData, DataMatchingRules $matchingRules) : array
    {
        return [];
    }
    /** **********************************************************************
     * fill matched data form matched items
     *
     * @param   array   &$result                        data to fill, link
     * @param   array   &$collectedData                 converted collected data, link
     * @param   array   $matchedItems                   matched data
     ************************************************************************/
    private function fillMatchedData(array &$result, array &$collectedData, array $matchedItems) : void
    {
        foreach ($matchedItems as $commonItemInfo)
        {
            $commonItem = [];

            foreach ($commonItemInfo as $participantCode => $participantItemId)
            {
                $commonItem[$participantCode] = $collectedData[$participantCode][$participantItemId];

                unset($collectedData[$participantCode][$participantItemId]);
                if (count($collectedData[$participantCode]) <= 0)
                {
                    unset($collectedData[$participantCode]);
                }
            }

            if (count($commonItem) > 0)
            {
                $result[] = $commonItem;
            }
        }
    }
    /** **********************************************************************
     * query matched items
     *
     * @param   Procedure $procedure                    procedure
     * @return  array                                   query result
     * @throws  RuntimeException                        querying error
     ************************************************************************/
    private function queryMatchedItems(Procedure $procedure) : array
    {
        try
        {
            $result         = [];
            $db             = DB::getInstance();
            $procedureCode  = $this->getObjectShortName($procedure);
            $queryString    = "
            SELECT
                matched_items.`ID`                                AS COMMON_ITEM_ID,
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
     * @param   array $newMatchedItems                  new matched items array
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
     * //TODO
     ************************************************************************/
    private function saveNewMatchedItems(array $newMatchedItems) : void
    {

    }
    /** **********************************************************************
     * get procedure participants map
     *
     * @param   Procedure $procedure                    procedure
     * @return  array                                   collected data as array
     * @example
     * [
     *      participantCode => participant,
     *      participantCode => participant
     * ]
     ************************************************************************/
    private function getParticipantsMap(Procedure $procedure) : array
    {
        $result             = [];
        $participantsSet    = $procedure->getParticipants();

        while ($participantsSet->valid())
        {
            $participant        = $participantsSet->current();
            $participantCode    = $this->getObjectShortName($participant);

            $result[$participantCode] = $participant;
            $participantsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * find participant ID field
     *
     * @param   Participant $participant                participant
     * @return  ParticipantField                        participant field
     * @throws  LogicException                          participant has no ID field
     ************************************************************************/
    private function findParticipantIdField(Participant $participant) : ParticipantField
    {
        $fieldsSet = $participant->getFields();

        while ($fieldsSet->valid())
        {
            $field = $fieldsSet->current();

            if ($field->getParam('type') == static::$idFieldType)
            {
               return  $field;
            }

            $fieldsSet->next();
        }

        throw new LogicException;
    }
    /** **********************************************************************
     * find participant item ID
     *
     * @param   ParticipantItem     $item               participant item
     * @param   ParticipantField    $field              participant field
     * @return  mixed                                   item ID
     * @throws  UnexpectedValueException                item ID incorrect or not found
     ************************************************************************/
    private function findParticipantItemId(ParticipantItem $item, ParticipantField $field)
    {
        if ($item->hasKey($field))
        {
            return $item->get($field);
        }

        throw new UnexpectedValueException;
    }
    /** **********************************************************************
     * get object short name
     *
     * @param   object $object                          object
     * @return  string                                  object short name
     ************************************************************************/
    private function getObjectShortName($object) : string
    {
        try
        {
            return (new ReflectionClass($object))->getShortName();
        }
        catch (ReflectionException $exception)
        {
            return is_object($object) ? get_class($object) : (string) $object;
        }
    }
    /** **********************************************************************
     * get object short name
     *
     * @param   string  $message                        message
     * @param   string  $type                           message type
     ************************************************************************/
    private function addLogMessage(string $message, string $type) : void
    {
        $logger         = Logger::getInstance();
        $procedure      = $this->getProcedure();
        $procedureCode  = $this->getObjectShortName($procedure);
        $fullMessage    = "Matcher working with procedure \"$procedureCode\": $message";

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