<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use UnitTests\TempDataGeneration\Exceptions\DBRecordsGenerationException;
/** ***********************************************************************************************
 * Class for creating project participants temp provided data
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantsDataGenerator
{
    private
        $structureGenerator     = null,
        $dbStructureGenerator   = null,
        $participantsExistData  = [],
        $participantsGetedData  = [],
        $proceduresMatchedData  = [],
        $proceduresCombinedData = [];
    /** **********************************************************************
     * constructor
     *
     * @param   StructureGenerator      $structureGenerator     structure generator
     * @param   DBStructureGenerator    $dbStructureGenerator   database structure generator
     ************************************************************************/
    public function __construct(StructureGenerator $structureGenerator, DBStructureGenerator $dbStructureGenerator)
    {
        $this->structureGenerator   = $structureGenerator;
        $this->dbStructureGenerator = $dbStructureGenerator;
    }
    /** **********************************************************************
     * generate project temp files structure
     *
     * @throws  DBRecordsGenerationException                    generating error
     ************************************************************************/
    public function generate() : void
    {
        try
        {
            $structureGenerator     = $this->structureGenerator;
            $dbStructureGenerator   = $this->dbStructureGenerator;
            $procedures             = array_keys($structureGenerator->getProcedures());

            foreach ($procedures as $procedureCode)
            {
                $matchingRules = $this->getFormatedMatchingRules($procedureCode, $structureGenerator);
/*
                foreach ($matchingRules as $rule)
                {
                    $existData      = $this->generateDataMatchedByRule($rule);
                    $getedData      = $this->generateDataMatchedByRule($rule);
                    $matchedData    = [];

                    foreach ($existData as $participantCode => $participantItemData)
                    {
                        $participantIdField = $participantsIdFields[$participantCode];
                        $participantItemId  = $participantItemData[$participantIdField];
                        $matchedData[$participantCode] = $participantItemId;
                    }
                }
*/
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get participant temp exist data structure
     *
     * @param   string  $participantCode                        participant code
     * @return  array                                           participant temp exist data structure
     * @example
     *  [
     *      participantItemId   => participantItemData,
     *      participantItemId   => participantItemData
     *  ]
     ************************************************************************/
    public function getParticipantExistData(string $participantCode) : array
    {
        return array_key_exists($participantCode, $this->participantsExistData)
            ? $this->participantsExistData[$participantCode]
            : [];
    }
    /** **********************************************************************
     * get participant temp geted data structure
     *
     * @param   string  $participantCode                        participant code
     * @return  array                                           participant temp geted data structure
     * @example
     *  [
     *      participantItemId   => participantItemData,
     *      participantItemId   => participantItemData
     *  ]
     ************************************************************************/
    public function getParticipantGetedData(string $participantCode) : array
    {
        return array_key_exists($participantCode, $this->participantsGetedData)
            ? $this->participantsGetedData[$participantCode]
            : [];
    }
    /** **********************************************************************
     * get procedure temp matched data structure
     *
     * @param   string $procedureCode                           procedure code
     * @return  array                                           procedure temp matched data structure
     * @example
     *  [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureMatchedData(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresMatchedData)
            ? $this->proceduresMatchedData[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * get procedure temp combined data structure
     *
     * @param   string $procedureCode                           procedure code
     * @return  array                                           procedure temp combined data structure
     * @example
     *  [
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ],
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureCombinedData(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresCombinedData)
            ? $this->proceduresCombinedData[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * clear temp files structure
     ************************************************************************/
    public function clear() : void
    {

    }
    /** **********************************************************************
     * get procedure formated matching rules
     *
     * @param   string              $procedureCode              procedure code
     * @param   StructureGenerator  $structureGenerator         structure generator
     * @return  array                                           procedure formated matching rules
     * @example
     *  [
     *      [
     *          participantCode => participantFieldStructure,
     *          participantCode => participantFieldStructure
     *      ],
     *      [
     *          participantCode => participantFieldStructure,
     *          participantCode => participantFieldStructure
     *      ]
     *  ]
     ************************************************************************/
    public function getFormatedMatchingRules(string $procedureCode, StructureGenerator $structureGenerator) : array
    {
        $result             = [];
        $matchingRules      = $structureGenerator->getProcedureMatchingRules($procedureCode);
        $procedureFields    = $structureGenerator->getProcedureFields($procedureCode);
        $participants       = $structureGenerator->getProcedureParticipants($procedureCode);
        $participantsFields = [];

        foreach ($participants as $participantCode)
        {
            $participantsFields[$participantCode] = $structureGenerator->getParticipantFields($participantCode);
        }

        foreach ($matchingRules as $ruleData)
        {
            $formatedRuleData   = [];
            $ruleParticipants   = $ruleData['participants'];

            foreach ($ruleData['fields'] as $procedureFieldName)
            {
                $procedureFieldData = array_key_exists($procedureFieldName, $procedureFields)
                    ? $procedureFields[$procedureFieldName]
                    : [];

                foreach ($ruleParticipants as $participantCode)
                {
                    if (array_key_exists($participantCode, $procedureFieldData))
                    {
                        $participantFieldName = $procedureFieldData[$participantCode];
                        if
                        (
                            array_key_exists($participantCode, $participantsFields) &&
                            array_key_exists($participantFieldName, $participantsFields[$participantCode])
                        )
                        {
                            $formatedRuleData[$participantCode] = $participantsFields[$participantCode][$participantFieldName];
                        }
                    }
                }
            }

            if (count($formatedRuleData) > 0)
            {
                $result[] = $formatedRuleData;
            }
        }

        return $result;
    }
}