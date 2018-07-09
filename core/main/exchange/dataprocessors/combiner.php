<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Fields\ProcedureField,
    Main\Exchange\Procedures\Exceptions\UnknownProcedureFieldException,
    Main\Exchange\DataProcessors\Data\CombinedItem,
    Main\Exchange\DataProcessors\Results\MatchedData,
    Main\Exchange\DataProcessors\Results\CombinedData;
/** ***********************************************************************************************
 * Combiner data-processor
 * combine items off different participants
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Combiner extends AbstractProcessor
{
    private
        $proceduresFieldsCollection = [],
        $combiningRulesCollection   = [],
        $matchedDataCollection      = [],
        $combinedDataCollection     = [];
    /** **********************************************************************
     * combine procedure participants data
     *
     * @param   MatchedData $matchedData        matched data
     * @return  CombinedData                    combined data
     ************************************************************************/
    public function combineItems(MatchedData $matchedData) : CombinedData
    {
        $result = new CombinedData;

        $this->addLogMessage('start combining data', 'notice');
        if ($matchedData->count() <= 0)
        {
            $this->addLogMessage('caught empty matched data', 'notice');
            return $result;
        }

        $this->proceduresFieldsCollection   = $this->getProceduresFieldsCollection();
        $this->combiningRulesCollection     = $this->getCombiningRulesCollection();
        $this->matchedDataCollection        = $this->getMatchedDataCollection($matchedData);
        $this->combinedDataCollection       = $this->getCombinedDataCollection();

        foreach ($this->combinedDataCollection as $commonItem)
        {
            $combinedItem = new CombinedItem;

            foreach ($commonItem as $procedureFieldName => $value)
            {
                try
                {
                    $procedureField = $this->findProcedureField($procedureFieldName);
                    $combinedItem->set($procedureField, $value);
                }
                catch (UnknownProcedureFieldException $exception)
                {
                    $procedureCode = $exception->getProcedureCode();
                    $this->addLogMessage("unknown procedure field in procedure \"$procedureCode\" on constructing combined data item", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $this->addLogMessage('unexpected error on constructing combined data item', 'warning');
                }
            }

            if ($combinedItem->count() == count($commonItem))
            {
                try
                {
                    $result->push($combinedItem);
                }
                catch (InvalidArgumentException $exception)
                {
                    $this->addLogMessage('unexpected error on constructing combined data item', 'warning');
                }
            }
        }

        if ($result->count() <= 0)
        {
            $this->addLogMessage('returning empty combined data while matched data is not empty', 'warning');
        }

        return $result;
    }
    /** **********************************************************************
     * get procedure fields collection
     *
     * @return  array                           procedure fields collection
     * @example
     * [
     *      procedureFieldName  => procedureField,
     *      procedureFieldName  => procedureField
     * ]
     ************************************************************************/
    private function getProceduresFieldsCollection() : array
    {
        $result             = [];
        $procedureFieldsSet = $this->getProcedure()->getFields();

        while ($procedureFieldsSet->valid())
        {
            $procedureField     = $procedureFieldsSet->current();
            $procedureFieldName = $this->getProcedureFieldName($procedureField);

            $result[$procedureFieldName] = $procedureField;
            $procedureFieldsSet->next();
        }

        return $result;
    }
    /** **********************************************************************
     * get data combining rules collection
     *
     * @return  array                           data combining rules collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    => participantFieldWeight,
     *          participantFieldName    => participantFieldWeight
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    => participantFieldWeight,
     *          participantFieldName    => participantFieldWeight
     *      ]
     * ]
     ************************************************************************/
    private function getCombiningRulesCollection() : array
    {
        $result         = [];
        $combiningRules = $this->getProcedure()->getDataCombiningRules();

        foreach ($combiningRules->getKeys() as $participantField)
        {
            $participantCode        = $participantField->getParticipant()->getCode();
            $participantFieldName   = $participantField->getField()->getParam('name');
            $weight                 = $combiningRules->get($participantField);

            if (!array_key_exists($participantCode, $result))
            {
                $result[$participantCode] = [];
            }
            $result[$participantCode][$participantFieldName] = $weight;
        }

        return $result;
    }
    /** **********************************************************************
     * get matched data collection
     *
     * @param   MatchedData $matchedData        matched data
     * @return  array                           matched data collection
     * @example
     * [
     *      [
     *          participantCode =>
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ],
     *          participantCode =>
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ]
     *      ],
     *      [
     *          participantCode =>
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ],
     *          participantCode =>
     *          [
     *              participantFieldName    => value,
     *              participantFieldName    => value
     *          ]
     *      ]
     * ]
     ************************************************************************/
    private function getMatchedDataCollection(MatchedData $matchedData) : array
    {
        $result = [];

        while ($matchedData->count() > 0)
        {
            try
            {
                $item       = $matchedData->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $participant)
                {
                    $valueItem          = $item->get($participant);
                    $participantCode    = $participant->getCode();

                    $itemArray[$participantCode] = [];
                    foreach ($valueItem->getKeys() as $participantField)
                    {
                        $value                  = $valueItem->get($participantField);
                        $participantFieldName   = $participantField->getParam('name');

                        $itemArray[$participantCode][$participantFieldName] = $value;
                    }
                }

                if (count($itemArray) > 0)
                {
                    $result[] = $itemArray;
                }
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * get combined data collection
     *
     * @return  array                           combined data collection
     * @example
     * [
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ],
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ]
     * ]
     ************************************************************************/
    private function getCombinedDataCollection() : array
    {
        $result = [];

        foreach ($this->matchedDataCollection as $itemData)
        {
            $itemArray = [];

            foreach ($this->proceduresFieldsCollection as $procedureFieldName => $procedureField)
            {
                try
                {
                    $values         = [];
                    $procedureField = $this->findProcedureField($procedureFieldName);

                    $procedureField->rewind();
                    while ($procedureField->valid())
                    {
                        $participantField       = $procedureField->current();
                        $participantFieldName   = $participantField->getField()->getParam('name');
                        $participantCode        = $participantField->getParticipant()->getCode();

                        if (array_key_exists($participantCode, $itemData) && array_key_exists($participantFieldName, $itemData[$participantCode]))
                        {
                            $value  = $itemData[$participantCode][$participantFieldName];
                            $weight =
                                array_key_exists($participantCode, $this->combiningRulesCollection) &&
                                array_key_exists($participantFieldName, $this->combiningRulesCollection[$participantCode])
                                    ? $this->combiningRulesCollection[$participantCode][$participantFieldName]
                                    : 0;

                            $values[$weight] = $value;
                        }

                        $procedureField->next();
                    }

                    $maxWeight  = count($values) > 0                    ? max(array_keys($values))  : 0;
                    $finalValue = array_key_exists($maxWeight, $values) ? $values[$maxWeight]       : null;

                    $itemArray[$procedureFieldName] = $finalValue;
                }
                catch (UnknownProcedureFieldException $exception)
                {

                }
            }

            if (count($itemArray) > 0)
            {
                $result[] = $itemArray;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * find procedure field by special index
     *
     * @param   string  $procedureFieldName         procedure field special index
     * @return  ProcedureField                      procedure field
     * @throws  UnknownProcedureFieldException      procedure field not found
     ************************************************************************/
    private function findProcedureField(string $procedureFieldName) : ProcedureField
    {
        if (array_key_exists($procedureFieldName, $this->proceduresFieldsCollection))
        {
            return $this->proceduresFieldsCollection[$procedureFieldName];
        }

        $exception      = new UnknownProcedureFieldException;
        $procedureCode  = $this->getProcedure()->getCode();
        $exception->setProcedureCode($procedureCode);

        throw $exception;
    }
    /** **********************************************************************
     * get procedure field special index
     *
     * @param   ProcedureField $field           procedure field
     * @return  string                          procedure field special index
     ************************************************************************/
    private function getProcedureFieldName(ProcedureField $field) : string
    {
        $fieldNameParts = [];

        $field->rewind();
        while ($field->valid())
        {
            $participantField       = $field->current();
            $participantCode        = $participantField->getParticipant()->getCode();
            $participantFieldName   = $participantField->getField()->getParam('name');

            $fieldNameParts[] = "$participantCode|$participantFieldName";
            $field->next();
        }

        return implode('||', $fieldNameParts);
    }
    /** **********************************************************************
     * get object short name
     *
     * @param   string  $message                message
     * @param   string  $type                   message type
     ************************************************************************/
    private function addLogMessage(string $message, string $type) : void
    {
        $logger         = Logger::getInstance();
        $procedureCode  = $this->getProcedure()->getCode();
        $fullMessage    = "Combiner for procedure \"$procedureCode\": $message";

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