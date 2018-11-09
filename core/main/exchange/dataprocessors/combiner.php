<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\Procedures\Fields\Field as ProcedureField,
    Main\Exchange\DataProcessors\Data\MatchedItem,
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
class Combiner
{
    private
        $procedure      = null,
        $procedureData  = null;
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure       $procedure      procedure
     * @param   ProcedureData   $data           procedure already exist data
     ************************************************************************/
    public function __construct(Procedure $procedure, ProcedureData $data)
    {
        $this->procedure        = $procedure;
        $this->procedureData    = $data;
    }
    /** **********************************************************************
     * combine procedure participants data
     *
     * @param   MatchedData $matchedData        matched data
     * @return  CombinedData                    combined data
     ************************************************************************/
    public function combineItems(MatchedData $matchedData) : CombinedData
    {
        $logger             = Logger::getInstance();
        $procedureCode      = $this->procedure->getCode();
        $logMessagePrefix   = "Combiner for procedure \"$procedureCode\"";
        $result             = new CombinedData;

        $logger->addNotice("$logMessagePrefix: start combining data");
        if ($matchedData->count() <= 0)
        {
            $logger->addNotice("$logMessagePrefix: caught empty matched data");
            return $result;
        }

        $procedureFieldsSet = $this->procedure->getFields();
        foreach ($matchedData->getKeys() as $commonItemId)
        {
            $combinedItem   = new CombinedItem;
            $matchedItem    = $matchedData->get($commonItemId);

            $procedureFieldsSet->rewind();
            while ($procedureFieldsSet->valid())
            {
                $procedureField         = $procedureFieldsSet->current();
                $procedureFieldValue    = $this->getProcedureFieldValue($procedureField, $matchedItem);

                try
                {
                    $combinedItem->set($procedureField, $procedureFieldValue);
                    $this->procedureData->setData($commonItemId, $procedureField, $procedureFieldValue);
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $logger->addWarning("$logMessagePrefix: unexpected error on constructing combined data, \"$error\"");
                }
                catch (RuntimeException $exception)
                {
                    $error = $exception->getMessage();
                    $logger->addWarning("$logMessagePrefix: error on saving data into procedure data container, \"$error\"");
                }
                $procedureFieldsSet->next();
            }

            try
            {
                $result->set($commonItemId, $combinedItem);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $logger->addWarning("$logMessagePrefix: unexpected error on constructing combined data, \"$error\"");
            }
        }

        if ($result->count() <= 0)
        {
            $logger->addWarning("$logMessagePrefix: returning empty combined data while matched data is not empty");
        }

        return $result;
    }
    /** **********************************************************************
     * get combined procedure field value
     *
     * @param   ProcedureField  $procedureField procedure field
     * @param   MatchedItem     $matchedItem    matched item
     * @return  mixed                           value
     ************************************************************************/
    private function getProcedureFieldValue(ProcedureField $procedureField, MatchedItem $matchedItem)
    {
        $combiningRules         = $this->procedure->getDataCombiningRules();
        $participantFieldsSet   = $procedureField->getParticipantsFields();
        $participantFieldValues = [];

        while ($participantFieldsSet->valid())
        {
            $participantField       = $participantFieldsSet->current();
            $participant            = $participantField->getParticipant();
            $participantItemData    = $matchedItem->hasKey($participant)
                ? $matchedItem->get($participant)
                : null;
            $participantFieldWeight = $combiningRules->hasKey($participantField)
                ? $combiningRules->get($participantField)
                : 0;

            if ($participantItemData && $participantItemData->hasKey($participantField))
            {
                $participantFieldValues[$participantFieldWeight] = $participantItemData->get($participantField);
            }

            $participantFieldsSet->next();
        }

        if (count($participantFieldValues) <= 0)
        {
            return null;
        }

        $maxWeight = max(array_keys($participantFieldValues));
        return $participantFieldValues[$maxWeight];
    }
    /** **********************************************************************
     * check value is empty
     *
     * @param   mixed $value                    value
     * @return  bool                            value is empty
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
}