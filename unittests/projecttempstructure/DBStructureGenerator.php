<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use RuntimeException;
/** ***********************************************************************************************
 * Class for creating project temp DB structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class DBStructureGenerator
{
    private $dbRecordsGenerator = null;
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->dbRecordsGenerator = new DBRecordsGenerator;
    }
    /** **********************************************************************
     * generate DB structure
     *
     * @param   array $structure                        generated logic structure
     * @return  array                                   generated DB structure
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    public function generate(array $structure) : array
    {
        $result = [];

        try
        {
            foreach ($this->generateProcedures($structure) as $procedureCode => $procedureId)
            {
                $procedureInfo = $structure[$procedureCode];
                $result[$procedureCode] = ['id' => $procedureId];

                $result[$procedureCode]['participants'] = [];
                foreach ($this->generateParticipants($procedureId, $procedureInfo) as $participantCode => $participantId)
                {
                    $participantInfo = $procedureInfo['participants'][$participantCode];
                    $result[$procedureCode]['participants'][$participantCode] =
                    [
                        'id'        => $participantId,
                        'fields'    => $this->generateParticipantFields($participantId, $participantInfo)
                    ];
                }

                $result[$procedureCode]['fields']               = $this->generateProcedureFields($result[$procedureCode], $procedureInfo);
                $result[$procedureCode]['dataMatchingRules']    = $this->generateDataMatchingRules($result[$procedureCode], $procedureInfo);

                $this->generateCombiningRules($result[$procedureCode], $procedureInfo);
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

       return $result;
    }
    /** **********************************************************************
     * clean temp DB records
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        $this->dbRecordsGenerator->clean();
    }
    /** **********************************************************************
     * generate procedures
     *
     * @param   array $structure                        generated logic structure
     * @return  array                                   generated DB procedures
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateProcedures(array $structure) : array
    {
        $result = [];

        try
        {
            foreach ($structure as $procedureCode => $procedureInfo)
            {
                $procedureId = $this->dbRecordsGenerator->generateRecord('procedures',
                [
                    'NAME'      => $procedureInfo['name'],
                    'CODE'      => $procedureInfo['code'],
                    'ACTIVITY'  => $procedureInfo['activity'] ? 'Y' : 'N'
                ]);

                $result[$procedureCode] = $procedureId;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants
     *
     * @param   int     $procedureId                    procedure ID
     * @param   array   $procedureStructure             generated procedure logic structure
     * @return  array                                   generated DB participants
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateParticipants(int $procedureId, array $procedureStructure) : array
    {
        $result = [];

        try
        {
            foreach ($procedureStructure['participants'] as $participantCode => $participantInfo)
            {
                $participantId = $this->dbRecordsGenerator->generateRecord('participants',
                [
                    'NAME'  => $participantInfo['name'],
                    'CODE'  => $participantInfo['code']
                ]);

                $this->dbRecordsGenerator->generateRecord('procedures_participants',
                [
                    'PROCEDURE'     => $procedureId,
                    'PARTICIPANT'   => $participantId
                ]);

                $result[$participantCode] = $participantId;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate participant fields
     *
     * @param   int     $participantId                  participant ID
     * @param   array   $participantStructure           generated participant logic structure
     * @return  array                                   generated DB participant fields
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateParticipantFields(int $participantId, array $participantStructure) : array
    {
        $result         = [];
        $fieldsTypes    = FieldsTypesManager::getFieldsTypes();

        try
        {
            foreach ($participantStructure['fields'] as $fieldName => $fieldInfo)
            {
                $fieldId = $this->dbRecordsGenerator->generateRecord('participants_fields',
                [
                    'NAME'          => $fieldInfo['name'],
                    'TYPE'          => $fieldsTypes[$fieldInfo['type']],
                    'IS_REQUIRED'   => $fieldInfo['required'] ? 'Y' : 'N',
                    'PARTICIPANT'   => $participantId
                ]);

                $result[$fieldName] = $fieldId;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure fields
     *
     * @param   array   $procedureGeneratedStructure    generated procedure DB structure
     * @param   array   $procedureLogicInfo             generated procedure logic structure
     * @return  array                                   generated DB procedure fields
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateProcedureFields(array $procedureGeneratedStructure, array $procedureLogicInfo) : array
    {
        $result = [];

        try
        {
            foreach ($procedureLogicInfo['fields'] as $procedureFieldName => $procedureFieldInfo)
            {
                $procedureFieldId = $this->dbRecordsGenerator->generateRecord('procedures_fields',
                [
                    'PROCEDURE' => $procedureGeneratedStructure['id']
                ]);

                foreach ($procedureFieldInfo as $participantCode => $participantFieldName)
                {
                    $participantFieldId = $procedureGeneratedStructure['participants'][$participantCode]['fields'][$participantFieldName];
                    $this->dbRecordsGenerator->generateRecord('procedures_participants_fields',
                    [
                        'PROCEDURE_FIELD'   => $procedureFieldId,
                        'PARTICIPANT_FIELD' => $participantFieldId
                    ]);
                }

                $result[$procedureFieldName] = $procedureFieldId;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate data matching rules
     *
     * @param   array   $procedureGeneratedStructure    generated procedure DB structure
     * @param   array   $procedureLogicInfo             generated procedure logic structure
     * @return  array                                   generated DB data matching rules
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateDataMatchingRules(array $procedureGeneratedStructure, array $procedureLogicInfo) : array
    {
        $result = [];

        try
        {
            foreach ($procedureLogicInfo['dataMatchingRules'] as $ruleName => $ruleInfo)
            {
                $ruleId = $this->dbRecordsGenerator->generateRecord('procedures_data_matching_rules',
                [
                    'PROCEDURE' => $procedureGeneratedStructure['id']
                ]);

                foreach ($ruleInfo['participants'] as $participantCode)
                {
                    $this->dbRecordsGenerator->generateRecord('procedures_data_matching_rules_participants',
                    [
                        'PARTICIPANT'   => $procedureGeneratedStructure['participants'][$participantCode]['id'],
                        'RULE'          => $ruleId
                    ]);
                }
                foreach ($ruleInfo['fields'] as $procedureFieldName)
                {
                    $this->dbRecordsGenerator->generateRecord('procedures_data_matching_rules_fields',
                    [
                        'PROCEDURE_FIELD'   => $procedureGeneratedStructure['fields'][$procedureFieldName],
                        'RULE'              => $ruleId
                    ]);
                }

                $result[$ruleName] = $ruleId;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate data combining rules
     *
     * @param   array   $procedureGeneratedStructure    generated procedure DB structure
     * @param   array   $procedureLogicInfo             generated procedure logic structure
     * @return  void
     * @throws  RuntimeException                        generating error
     ************************************************************************/
    private function generateCombiningRules(array $procedureGeneratedStructure, array $procedureLogicInfo) : void
    {
        try
        {
            foreach ($procedureLogicInfo['dataCombiningRules'] as $participantCode => $participantFields)
            {
                foreach ($participantFields as $participantFieldName => $weight)
                {
                    $participantFieldId = $procedureGeneratedStructure['participants'][$participantCode]['fields'][$participantFieldName];

                    $this->dbRecordsGenerator->generateRecord('procedures_data_combining_rules',
                    [
                        'PROCEDURE'         => $procedureGeneratedStructure['id'],
                        'PARTICIPANT_FIELD' => $participantFieldId,
                        'WEIGHT'            => $weight
                    ]);
                }
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
}