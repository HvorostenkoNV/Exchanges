<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use
    RuntimeException,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    UnitTests\TempDataGeneration\Exceptions\DBRecordsGenerationException,
    Main\Helpers\Database\DB,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager;
/** ***********************************************************************************************
 * Class for creating project temp database structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class DBStructureGenerator
{
    private
        $structureGenerator = null,
        $fieldsTypesIdMap   = [],
        $procedures         = [],
        $participants       = [],
        $participantsFields = [],
        $proceduresFields   = [];
    /** **********************************************************************
     * constructor
     *
     * @param   StructureGenerator $structureGenerator      structure generator
     ************************************************************************/
    public function __construct(StructureGenerator $structureGenerator)
    {
        $this->structureGenerator   = $structureGenerator;
        $this->fieldsTypesIdMap     = $this->queryFieldsTypesIdMap();
    }
    /** **********************************************************************
     * generate project temp database structure
     *
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    public function generate() : void
    {
        $structureGenerator = $this->structureGenerator;

        try
        {
            $this->procedures   = $this->generateProcedures($structureGenerator->getProcedures());
            $this->participants = $this->generateParticipants($structureGenerator->getParticipants());

            foreach ($this->participants as $participantCode => $participantId)
            {
                $this->participantsFields[$participantCode] = $this->generateParticipantFields
                (
                    $participantId,
                    $structureGenerator->getParticipantFields($participantCode)
                );
            }

            foreach ($this->procedures as $procedureCode => $procedureId)
            {
                $this->generateProcedureParticipantsRelations
                (
                    $procedureId,
                    $structureGenerator->getProcedureParticipants($procedureCode),
                    $this->participants
                );
                $this->proceduresFields[$procedureCode] = $this->generateProcedureFields
                (
                    $procedureId,
                    $structureGenerator->getProcedureFields($procedureCode),
                    $this->participantsFields
                );
                $this->generateProcedureMatchingRules
                (
                    $procedureId,
                    $structureGenerator->getProcedureMatchingRules($procedureCode),
                    $this->proceduresFields[$procedureCode],
                    $this->participants
                );
                $this->generateProcedureCombiningRules
                (
                    $procedureId,
                    $structureGenerator->getProcedureCombiningRules($procedureCode),
                    $this->participantsFields
                );
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * clear temp database structure
     ************************************************************************/
    public function clear() : void
    {
        try
        {
            $db                         = DB::getInstance();
            $proceduresIdPlaceholder    = rtrim(str_repeat('?, ', count($this->procedures)),    ', ');
            $participantsIdPlaceholder  = rtrim(str_repeat('?, ', count($this->participants)),  ', ');

            $db->query
            (
                "DELETE FROM `procedures` WHERE `ID` IN ($proceduresIdPlaceholder)",
                array_values($this->procedures)
            );
            $db->query
            (
                "DELETE FROM `participants` WHERE `ID` IN ($participantsIdPlaceholder)",
                array_values($this->participants)
            );
        }
        catch (DBConnectionException $exception)
        {

        }
    }
    /** **********************************************************************
     * get procedure database id record
     *
     * @param   string $procedureCode                       procedure code
     * @return  int                                         procedure id
     ************************************************************************/
    public function getProcedureId(string $procedureCode) : int
    {
        return array_key_exists($procedureCode, $this->procedures)
            ? $this->procedures[$procedureCode]
            : 0;
    }
    /** **********************************************************************
     * get participant database id record
     *
     * @param   string $participantCode                     participant code
     * @return  int                                         participant id
     ************************************************************************/
    public function getParticipantId(string $participantCode) : int
    {
        return array_key_exists($participantCode, $this->participants)
            ? $this->participants[$participantCode]
            : 0;
    }
    /** **********************************************************************
     * get procedure field database id record
     *
     * @param   string  $procedureCode                      procedure code
     * @param   string  $procedureFieldName                 procedure field name
     * @return  int                                         procedure field id
     ************************************************************************/
    public function getProcedureFieldId(string $procedureCode, string $procedureFieldName) : int
    {
        return
            array_key_exists($procedureCode, $this->proceduresFields) &&
            array_key_exists($procedureFieldName, $this->proceduresFields[$procedureCode])
                ? $this->proceduresFields[$procedureCode][$procedureFieldName]
                : 0;
    }
    /** **********************************************************************
     * get participant field database id record
     *
     * @param   string  $participantCode                    participant code
     * @param   string  $participantFieldName               participant field name
     * @return  int                                         participant field id
     ************************************************************************/
    public function getParticipantFieldId(string $participantCode, string $participantFieldName) : int
    {
        return
            array_key_exists($participantCode, $this->participantsFields) &&
            array_key_exists($participantFieldName, $this->participantsFields[$participantCode])
                ? $this->participantsFields[$participantCode][$participantFieldName]
                : 0;
    }
    /** **********************************************************************
     * generate procedures database structure
     *
     * @param   array $proceduresStructure                  procedures structure
     * @example
     *  [
     *      procedureCode   => procedureStructure,
     *      procedureCode   => procedureStructure,
     *  ]
     * @return  array                                       procedures database structure
     * @example
     *  [
     *      procedureCode   => procedureId,
     *      procedureCode   => procedureId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateProcedures(array $proceduresStructure) : array
    {
        $result = [];

        try
        {
            foreach ($proceduresStructure as $procedureCode => $procedureStructure)
            {
                $procedureId = $this->makeTableRecord('procedures',
                    [
                        'NAME'      => $procedureStructure['name'],
                        'CODE'      => $procedureStructure['code'],
                        'ACTIVITY'  => $procedureStructure['activity'] ? 'Y' : 'N'
                    ]);

                $result[$procedureCode] = $procedureId;
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants database structure
     *
     * @param   array $participantsStructure                participants structure
     * @example
     *  [
     *      participantCode => participantsStructure,
     *      participantCode => participantsStructure
     *  ]
     * @return  array                                       participants database structure
     * @example
     *  [
     *      participantCode => participantId,
     *      participantCode => participantId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateParticipants(array $participantsStructure) : array
    {
        $result = [];

        try
        {
            foreach ($participantsStructure as $participantCode => $participantStructure)
            {
                $participantId = $this->makeTableRecord('participants',
                    [
                        'NAME'  => $participantStructure['name'],
                        'CODE'  => $participantStructure['code']
                    ]);

                $result[$participantCode] = $participantId;
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure participants relations
     *
     * @param   int         $procedureId                    procedure ID
     * @param   string[]    $procedureParticipants          procedure participants
     * @param   array       $participantsDbStructure        participants database structure
     * @example
     *  [
     *      participantCode => participantId,
     *      participantCode => participantId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateProcedureParticipantsRelations(int $procedureId, array $procedureParticipants, array $participantsDbStructure) : void
    {
        try
        {
            foreach ($procedureParticipants as $participantCode)
            {
                if (!array_key_exists($participantCode, $participantsDbStructure))
                {
                    throw new DBRecordsGenerationException("Participant ID for \"$participantCode\" not found");
                }

                $hasDuplicateRecord = rand(1, 4) == 4;
                $recordsCount       = $hasDuplicateRecord ? rand(2, 4) : 1;

                for ($index = $recordsCount; $index > 0; $index--)
                {
                    $this->makeTableRecord('procedures_participants',
                        [
                            'PROCEDURE'     => $procedureId,
                            'PARTICIPANT'   => $participantsDbStructure[$participantCode]
                        ]);
                }
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * generate participant fields database structure
     *
     * @param   int     $participantId                      participant ID
     * @param   array   $fieldsStructure                    participants fields structure
     * @example
     *  [
     *      participantFieldName    => participantFieldStructure,
     *      participantFieldName    => participantFieldStructure
     *  ]
     * @return  array                                       participant fields database structure
     * @example
     *  [
     *      participantFieldName    => participantFieldId,
     *      participantFieldName    => participantFieldId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateParticipantFields(int $participantId, array $fieldsStructure) : array
    {
        $result = [];

        try
        {
            foreach ($fieldsStructure as $participantFieldName => $participantFieldStructure)
            {
                $fieldType      = $participantFieldStructure['type'];
                $fieldRequired  = $participantFieldStructure['required'] ? 'Y' : 'N';

                if (!array_key_exists($fieldType, $this->fieldsTypesIdMap))
                {
                    throw new DBRecordsGenerationException("Field type ID for \"$fieldType\" not found");
                }
                if ($fieldType == FieldsTypesManager::ID_FIELD_TYPE && rand(1, 4) == 4)
                {
                    $fieldRequired = 'N';
                }

                $participantFieldId = $this->makeTableRecord('participants_fields',
                    [
                        'NAME'          => $participantFieldStructure['name'],
                        'TYPE'          => $this->fieldsTypesIdMap[$fieldType],
                        'IS_REQUIRED'   => $fieldRequired,
                        'PARTICIPANT'   => $participantId
                    ]);

                $result[$participantFieldName] = $participantFieldId;
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure fields database structure
     *
     * @param   int     $procedureId                        procedure ID
     * @param   array   $procedureFieldsStructure           procedure fields structure
     * @example
     *  [
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ],
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *  ]
     * @param   array   $participantsFieldsDbStructure      participants fields database structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldId,
     *              participantFieldName    => participantFieldId
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldId,
     *              participantFieldName    => participantFieldId
     *          ]
     *  ]
     * @return  array                                       procedure fields database structure
     * @example
     *  [
     *      procedureFieldName  => procedureFieldId,
     *      procedureFieldName  => procedureFieldId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateProcedureFields(int $procedureId, array $procedureFieldsStructure, array $participantsFieldsDbStructure) : array
    {
        $result             = [];
        $emptyFieldsCount   = rand(0, 5);

        try
        {
            foreach ($procedureFieldsStructure as $procedureFieldName => $procedureFieldStructure)
            {
                $procedureFieldId = $this->makeTableRecord('procedures_fields',
                    [
                        'PROCEDURE' => $procedureId
                    ]);

                foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
                {
                    if
                    (
                        !array_key_exists($participantCode, $participantsFieldsDbStructure) ||
                        !array_key_exists($participantFieldName, $participantsFieldsDbStructure[$participantCode])
                    )
                    {
                        throw new DBRecordsGenerationException("Participant field ID for \"$participantFieldName\" in \"$participantCode\" participant not found");
                    }

                    $hasDuplicateRecord = rand(1, 4) == 4;
                    $recordsCount       = $hasDuplicateRecord ? rand(2, 4) : 1;

                    for ($index = $recordsCount; $index > 0; $index--)
                    {
                        $this->makeTableRecord('procedures_participants_fields',
                            [
                                'PROCEDURE_FIELD'   => $procedureFieldId,
                                'PARTICIPANT_FIELD' => $participantsFieldsDbStructure[$participantCode][$participantFieldName]
                            ]);
                    }
                }

                $result[$procedureFieldName] = $procedureFieldId;
            }

            for ($index = $emptyFieldsCount; $index > 0; $index--)
            {
                $this->makeTableRecord('procedures_fields',
                    [
                        'PROCEDURE' => $procedureId
                    ]);
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure fields database structure
     *
     * @param   int     $procedureId                        procedure ID
     * @param   array   $procedureMatchingRulesStructure    procedure matching rules structure
     * @example
     *  [
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ],
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ]
     *  ]
     * @param   array   $procedureFieldsDbStructure         procedure fields database structure
     * @example
     *  [
     *      procedureFieldName  => procedureFieldId,
     *      procedureFieldName  => procedureFieldId
     *  ]
     * @param   array   $participantsDbStructure            participants database structure
     * @example
     *  [
     *      participantCode => participantId,
     *      participantCode => participantId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateProcedureMatchingRules(int $procedureId, array $procedureMatchingRulesStructure, array $procedureFieldsDbStructure, array $participantsDbStructure) : void
    {
        try
        {
            foreach ($procedureMatchingRulesStructure as $rule)
            {
                $ruleId = $this->makeTableRecord('procedures_data_matching_rules',
                    [
                        'PROCEDURE' => $procedureId
                    ]);

                foreach ($rule['participants'] as $participantCode)
                {
                    if (!array_key_exists($participantCode, $participantsDbStructure))
                    {
                        throw new DBRecordsGenerationException("Participant ID for \"$participantCode\" not found");
                    }

                    $hasDuplicateRecord = rand(1, 4) == 4;
                    $recordsCount       = $hasDuplicateRecord ? rand(2, 4) : 1;

                    for ($index = $recordsCount; $index > 0; $index--)
                    {
                        $this->makeTableRecord('procedures_data_matching_rules_participants',
                            [
                                'PARTICIPANT'   => $participantsDbStructure[$participantCode],
                                'RULE'          => $ruleId
                            ]);
                    }
                }

                foreach ($rule['fields'] as $procedureFieldName)
                {
                    if (!array_key_exists($procedureFieldName, $procedureFieldsDbStructure))
                    {
                        throw new DBRecordsGenerationException("Procedure field ID for \"$procedureFieldName\" not found");
                    }

                    $hasDuplicateRecord = rand(1, 4) == 4;
                    $recordsCount       = $hasDuplicateRecord ? rand(2, 4) : 1;

                    for ($index = $recordsCount; $index > 0; $index--)
                    {
                        $this->makeTableRecord('procedures_data_matching_rules_fields',
                            [
                                'PROCEDURE_FIELD'   => $procedureFieldsDbStructure[$procedureFieldName],
                                'RULE'              => $ruleId
                            ]);
                    }
                }
            }

            $this->generateIncorrectProcedureMatchingRules($procedureId, $procedureFieldsDbStructure, $participantsDbStructure);
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * generate incorrect procedure matching rules database structure
     *
     * @param   int     $procedureId                        procedure ID
     * @param   array   $procedureFieldsDbStructure         procedure fields database structure
     * @example
     *  [
     *      procedureFieldName  => procedureFieldId,
     *      procedureFieldName  => procedureFieldId
     *  ]
     * @param   array   $participantsDbStructure            participants database structure
     * @example
     *  [
     *      participantCode => participantId,
     *      participantCode => participantId
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateIncorrectProcedureMatchingRules(int $procedureId, array $procedureFieldsDbStructure, array $participantsDbStructure) : void
    {
        if (count($procedureFieldsDbStructure) <= 0 || count($participantsDbStructure) < 2)
        {
            return;
        }

        try
        {
            $emptyRulesCount                = 5;
            $rulesWithNoParticipantsCount   = 5;
            $rulesWithNoFieldsCount         = 5;
            $rulesWithOneParticipantCount   = 5;

            for ($index = $emptyRulesCount; $index > 0; $index--)
            {
                $this->makeTableRecord('procedures_data_matching_rules',
                    [
                        'PROCEDURE' => $procedureId
                    ]);
            }

            for ($index = $rulesWithNoParticipantsCount; $index > 0; $index--)
            {
                $ruleFieldsCount    = rand(1, count($procedureFieldsDbStructure) >= 4 ? 4 : count($procedureFieldsDbStructure));
                $ruleFields         = (array) array_rand($procedureFieldsDbStructure, $ruleFieldsCount);
                $ruleId             = $this->makeTableRecord('procedures_data_matching_rules',
                    [
                        'PROCEDURE' => $procedureId
                    ]);

                foreach ($ruleFields as $procedureFieldName)
                {
                    $this->makeTableRecord('procedures_data_matching_rules_fields',
                        [
                            'PROCEDURE_FIELD'   => $procedureFieldsDbStructure[$procedureFieldName],
                            'RULE'              => $ruleId
                        ]);
                }
            }

            for ($index = $rulesWithNoFieldsCount; $index > 0; $index--)
            {
                $ruleParticipantsCount  = rand(2, count($participantsDbStructure) >= 4 ? 4 : count($participantsDbStructure));
                $ruleParticipants       = array_rand($participantsDbStructure, $ruleParticipantsCount);
                $ruleId                 = $this->makeTableRecord('procedures_data_matching_rules',
                    [
                        'PROCEDURE' => $procedureId
                    ]);

                foreach ($ruleParticipants as $participantCode)
                {
                    $this->makeTableRecord('procedures_data_matching_rules_participants',
                        [
                            'PARTICIPANT'   => $participantsDbStructure[$participantCode],
                            'RULE'          => $ruleId
                        ]);
                }
            }

            for ($index = $rulesWithOneParticipantCount; $index > 0; $index--)
            {
                $ruleFieldsCount    = rand(1, count($procedureFieldsDbStructure) >= 4 ? 4 : count($procedureFieldsDbStructure));
                $ruleFields         = (array) array_rand($procedureFieldsDbStructure, $ruleFieldsCount);
                $ruleParticipant    = array_rand($participantsDbStructure);
                $ruleId             = $this->makeTableRecord('procedures_data_matching_rules',
                    [
                        'PROCEDURE' => $procedureId
                    ]);

                $this->makeTableRecord('procedures_data_matching_rules_participants',
                    [
                        'PARTICIPANT'   => $participantsDbStructure[$ruleParticipant],
                        'RULE'          => $ruleId
                    ]);

                foreach ($ruleFields as $procedureFieldName)
                {
                    $this->makeTableRecord('procedures_data_matching_rules_fields',
                        [
                            'PROCEDURE_FIELD'   => $procedureFieldsDbStructure[$procedureFieldName],
                            'RULE'              => $ruleId
                        ]);
                }
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * generate procedure fields database structure
     *
     * @param   int     $procedureId                        procedure ID
     * @param   array   $procedureCombiningRulesStructure   procedure combining rules structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ]
     *  ]
     * @param   array   $participantFieldsDbStructure       participant fields database structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldId,
     *              participantFieldName    => participantFieldId
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldId,
     *              participantFieldName    => participantFieldId
     *          ]
     *  ]
     * @throws  DBRecordsGenerationException                generation error
     ************************************************************************/
    private function generateProcedureCombiningRules(int $procedureId, array $procedureCombiningRulesStructure, array $participantFieldsDbStructure) : void
    {
        try
        {
            foreach ($procedureCombiningRulesStructure as $participantCode => $participantFields)
            {
                foreach ($participantFields as $participantFieldName => $participantFieldWeight)
                {
                    if
                    (
                        !array_key_exists($participantCode, $participantFieldsDbStructure) ||
                        !array_key_exists($participantFieldName, $participantFieldsDbStructure[$participantCode])
                    )
                    {
                        throw new DBRecordsGenerationException("Participant field ID for \"$participantFieldName\" in \"$participantCode\" participant not found");
                    }

                    $hasDuplicateRecord = rand(1, 4) == 4;
                    $recordsCount       = $hasDuplicateRecord ? rand(2, 4) : 1;

                    for ($index = $recordsCount; $index > 0; $index--)
                    {
                        $this->makeTableRecord('procedures_data_combining_rules',
                            [
                                'PROCEDURE'         => $procedureId,
                                'PARTICIPANT_FIELD' => $participantFieldsDbStructure[$participantCode][$participantFieldName],
                                'WEIGHT'            => $participantFieldWeight
                            ]);
                    }
                }
            }
        }
        catch (DBRecordsGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query fields types ID map
     *
     * @return  array                                       fields types ID map
     * @example
     *  [
     *      fieldTypeCode   => fieldTypeId,
     *      fieldTypeCode   => fieldTypeId
     *  ]
     ************************************************************************/
    private function queryFieldsTypesIdMap() : array
    {
        $result         = [];
        $queryResult    = null;

        try
        {
            $queryResult = DB::getInstance()->query("SELECT `ID`, `CODE` FROM fields_types");
        }
        catch (DBConnectionException $exception)
        {
            return $result;
        }
        catch (DBQueryException $exception)
        {
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            try
            {
                $item = $queryResult->pop();
                $result[$item->get('CODE')] = (int) $item->get('ID');
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * make table record
     *
     * @param   string  $tableName                          table name
     * @param   array   $item                               item values
     * @return  int                                         new record id
     * @throws  DBRecordsGenerationException                database writing error
     ************************************************************************/
    private function makeTableRecord(string $tableName, array $item) : int
    {
        try
        {
            $db                 = DB::getInstance();
            $fields             = array_map
            (
                function($value) {return "`$value`";},
                array_keys($item)
            );
            $fieldsPlaceholder  = implode(', ', $fields);
            $valuesPlaceholder  = rtrim(str_repeat('?, ', count($item)), ', ');
            $sqlQuery           = "INSERT INTO $tableName ($fieldsPlaceholder) VALUES ($valuesPlaceholder)";

            $db->query($sqlQuery, array_values($item));
            $recordId = $db->getLastInsertId();

            if ($recordId <= 0)
            {
                throw new DBConnectionException('new record was not created with unknown error');
            }

            return $recordId;
        }
        catch (DBConnectionException $exception)
        {
            throw new DBRecordsGenerationException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw new DBRecordsGenerationException($exception->getMessage());
        }
    }
}