<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    RuntimeException,
    InvalidArgumentException,
    ReflectionException,
    ReflectionClass,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\ParticipantField,
    Main\Exchange\Procedures\Fields\ProcedureField,
    Main\Exchange\Procedures\Fields\FieldsSet,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Procedures\Rules\DataCombiningRules,
    Main\Exchange\Procedures\Exceptions\UnknownProcedureFieldException;
/** ***********************************************************************************************
 * Application procedure abstract class
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractProcedure implements Procedure
{
    private
        $code                           = '',
        $participantsCollection         = [],
        $participantsFieldsCollection   = [],
        $procedureFieldsCollection      = [],
        $dataMatchingRulesCollection    = [],
        $dataCombiningRulesCollection   = [];
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        try
        {
            $reflection = new ReflectionClass(static::class);
            $this->code = $reflection->getShortName();
        }
        catch (ReflectionException $exception)
        {

        }

        $this->participantsCollection       = $this->getParticipantsCollection();
        $this->participantsFieldsCollection = $this->getParticipantsFieldsCollection();
        $this->procedureFieldsCollection    = $this->getProcedureFieldsCollection();
        $this->dataMatchingRulesCollection  = $this->getDataMatchingRulesCollection();
        $this->dataCombiningRulesCollection = $this->getDataCombiningRulesCollection();

        $this->addLogMessage('created', 'notice');
    }
    /** **********************************************************************
     * get procedure code
     *
     * @return  string                              procedure code
     ************************************************************************/
    public function getCode() : string
    {
        return $this->code;
    }
    /** **********************************************************************
     * get procedure participants set
     *
     * @return  ParticipantsSet                     procedure participants set
     ************************************************************************/
    final public function getParticipants() : ParticipantsSet
    {
        $result = new ParticipantsSet;

        if (count($this->participantsCollection) <= 0)
        {
            $this->addLogMessage('has no participants', 'warning');
        }

        foreach ($this->participantsCollection as $participant)
        {
            try
            {
                $result->push($participant);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unknown error on constructing participants set, \"$error\"", 'warning');
            }
        }

        $this->addLogMessage('participants set constructed and returned', 'notice');
        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * get procedure fields set
     *
     * @return  FieldsSet                           procedure fields set
     ************************************************************************/
    final public function getFields() : FieldsSet
    {
        $result = new FieldsSet;

        if (count($this->procedureFieldsCollection) <= 0)
        {
            $this->addLogMessage('has no fields', 'warning');
        }

        foreach ($this->procedureFieldsCollection as $field)
        {
            try
            {
                $field->rewind();
                $result->push($field);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unknown error on constructing fields set, \"$error\"", 'warning');
            }
        }

        $this->addLogMessage('fields set constructed and returned', 'notice');
        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * get procedure data matching rules
     *
     * @return  DataMatchingRules                   procedure data matching rules
     ************************************************************************/
    final public function getDataMatchingRules() : DataMatchingRules
    {
        $result = new DataMatchingRules;

        if (count($this->dataMatchingRulesCollection) <= 0)
        {
            $this->addLogMessage('has no data matching rules', 'warning');
        }

        foreach ($this->dataMatchingRulesCollection as $rule)
        {
            try
            {
                $participantsSet    = new ParticipantsSet;
                $fieldsSet          = new FieldsSet;

                foreach ($rule['participants'] as $participantCode)
                {
                    $participant = $this->findParticipant($participantCode);
                    $participantsSet->push($participant);
                }
                foreach ($rule['fields'] as $procedureFieldId)
                {
                    $procedureField = $this->findProcedureField((int) $procedureFieldId);
                    $procedureField->rewind();
                    $fieldsSet->push($procedureField);
                }

                $participantsSet->rewind();
                $fieldsSet->rewind();
                $result->set($participantsSet, $fieldsSet);
            }
            catch (UnknownParticipantException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("unknown participant \"$participantCode\" on constructing data matching rules set", 'warning');
            }
            catch (UnknownProcedureFieldException $exception)
            {
                $this->addLogMessage("unknown procedure field on constructing data matching rules set", 'warning');
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unknown error on constructing data matching rules set, \"$error\"", 'warning');
            }
        }

        $this->addLogMessage('data matching rules set constructed and returned', 'notice');
        return $result;
    }
    /** **********************************************************************
     * get data combining rules
     *
     * @return  DataCombiningRules                  procedure data combining rules
     ************************************************************************/
    final public function getDataCombiningRules() : DataCombiningRules
    {
        $result = new DataCombiningRules;

        if (count($this->dataCombiningRulesCollection) <= 0)
        {
            $this->addLogMessage('has no data combining rules', 'warning');;
        }

        foreach ($this->dataCombiningRulesCollection as $participantCode => $participantFields)
        {
            foreach ($participantFields as $participantFieldName => $weight)
            {
                try
                {
                    $participantField = $this->findParticipantField($participantCode, $participantFieldName);
                    $result->set($participantField, $weight);
                }
                catch (UnknownParticipantFieldException $exception)
                {
                    $this->addLogMessage("unknown participant field \"$participantFieldName\" in \"$participantCode\" on constructing data combining rules set", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unknown error on constructing data combining rules set, \"$error\"", 'warning');
                }
            }
        }

        $this->addLogMessage('data combining rules set constructed and returned', 'notice');
        return $result;
    }
    /** **********************************************************************
     * get participants collection
     *
     * @return  array                               procedure participants collection
     * @example
     * [
     *      participantCode => participant,
     *      participantCode => participant
     * ]
     ************************************************************************/
    private function getParticipantsCollection() : array
    {
        $result         = [];
        $queryResult    = [];

        try
        {
            $queryResult = $this->queryParticipants();
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("failed to query participants, \"$error\"", 'warning');
        }

        foreach ($queryResult as $item)
        {
            try
            {
                $result[$item['CODE']] = $this->createParticipant($item['CODE']);
            }
            catch (UnknownParticipantException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("failed to create participant \"$participantCode\"", 'warning');
            }
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
     *          participantFieldName    => procedureParticipantField,
     *          participantFieldName    => procedureParticipantField
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    => procedureParticipantField,
     *          participantFieldName    => procedureParticipantField
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
                    $participantField           = $participantFields->current();
                    $participantFieldName       = $participantField->getParam('name');
                    $procedureParticipantField  = new ParticipantField($participant, $participantField);

                    $result[$participantCode][$participantFieldName] = $procedureParticipantField;
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
     * get procedure fields collection
     *
     * @return  array                               procedure fields collection
     * @example
     * [
     *      procedureFieldId    => procedureField,
     *      procedureFieldId    => procedureField
     * ]
     ************************************************************************/
    private function getProcedureFieldsCollection() : array
    {
        $result         = [];
        $queryResult    = [];

        try
        {
            $participantsCodeArray  = array_keys($this->participantsCollection);
            $queryResult            = $this->queryProcedureFields($participantsCodeArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("failed to query procedure fields, \"$error\"", 'warning');
        }

        foreach ($queryResult as $item)
        {
            $procedureField     = new ProcedureField;
            $procedureFieldId   = $item['ID'];

            try
            {
                foreach ($item['PARTICIPANTS_FIELDS'] as $participantCode => $participantFieldName)
                {
                    $participantField = $this->findParticipantField($participantCode, $participantFieldName);
                    $procedureField->push($participantField);
                }
            }
            catch (UnknownParticipantFieldException $exception)
            {
                $participantCode        = $exception->getParticipantCode();
                $participantFieldName   = $exception->getParticipantFieldName();
                $this->addLogMessage("caught unknown participant field\"$participantFieldName\" in participant \"$participantCode\" on constructing procedure field \"$procedureFieldId\"", 'warning');
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("caught error on constructing procedure field \"$procedureFieldId\", \"$error\"", 'warning');
            }

            if ($procedureField->count() > 0)
            {
                $procedureField->rewind();
                $result[$procedureFieldId] = $procedureField;
            }
            else
            {
                $this->addLogMessage("procedure field \"$procedureFieldId\" has no participants fields", 'warning');
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
     *          'participants'  => [participantCode, participantCode, participantCode],
     *          'fields'        => [procedureFieldId, procedureFieldId, procedureFieldId]
     *      ],
     *      [
     *          'participants'  => [participantCode, participantCode, participantCode],
     *          'fields'        => [procedureFieldId, procedureFieldId, procedureFieldId]
     *      ]
     * ]
     ************************************************************************/
    private function getDataMatchingRulesCollection() : array
    {
        $result         = [];
        $queryResult    = [];

        try
        {
            $participantsCodeArray  = array_keys($this->participantsCollection);
            $procedureFieldsIdArray = array_keys($this->procedureFieldsCollection);
            $queryResult            = $this->queryProcedureDataMatchingRules($participantsCodeArray, $procedureFieldsIdArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("failed to query data matching rules, \"$error\"", 'warning');
        }

        foreach ($queryResult as $item)
        {
            $ruleId                 = $item['ID'];
            $ruleParticipants       = $item['PARTICIPANTS'];
            $ruleProcedureFields    = $item['PROCEDURE_FIELDS'];

            if (!is_array($ruleParticipants) || count($ruleParticipants) <= 0)
            {
                $this->addLogMessage("matching rule \"$ruleId\" has no participants", 'warning');
                continue;
            }
            if (!is_array($ruleProcedureFields) || count($ruleProcedureFields) <= 0)
            {
                $this->addLogMessage("matching rule \"$ruleId\" has no participants fields", 'warning');
                continue;
            }

            $result[$ruleId] =
                [
                    'participants'  => $ruleParticipants,
                    'fields'        => $ruleProcedureFields
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * get data combining rules collection
     *
     * @return  array                               data combining rules collection
     * @example
     * [
     *      participantCode =>
     *      [
     *          participantFieldName    => weight,
     *          participantFieldName    => weight
     *      ],
     *      participantCode =>
     *      [
     *          participantFieldName    => weight,
     *          participantFieldName    => weight
     *      ]
     * ]
     ************************************************************************/
    private function getDataCombiningRulesCollection() : array
    {
        $result         = [];
        $queryResult    = [];

        try
        {
            $participantsCodeArray  = array_keys($this->participantsCollection);
            $queryResult            = $this->queryProcedureDataCombiningRules($participantsCodeArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("failed to query data combining rules, \"$error\"", 'warning');
        }

        foreach ($queryResult as $item)
        {
            $participantCode        = $item['PARTICIPANT_CODE'];
            $participantFieldName   = $item['FIELD_NAME'];
            $weight                 = (int) $item['WEIGHT'];

            if (!array_key_exists($participantCode, $result))
            {
                $result[$participantCode] = [];
            }

            $result[$participantCode][$participantFieldName] = $weight;
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
     * @throws  UnknownParticipantFieldException    participant field not found
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
     * find procedure field
     *
     * @param   int $procedureFieldId               procedure field id
     * @return  ProcedureField                      procedure field
     * @throws  UnknownProcedureFieldException      procedure field not found
     ************************************************************************/
    private function findProcedureField(int $procedureFieldId) : ProcedureField
    {
        if (array_key_exists($procedureFieldId, $this->procedureFieldsCollection))
        {
            return $this->procedureFieldsCollection[$procedureFieldId];
        }

        $exception = new UnknownProcedureFieldException;
        $exception->setProcedureCode($this->getCode());
        throw $exception;
    }
    /** **********************************************************************
     * query procedure participants
     *
     * @return  array                               procedure participant query result
     * @example
     * [
     *      ['CODE'  => participantCode],
     *      ['CODE'  => participantCode]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryParticipants() : array
    {
        try
        {
            $sqlQuery = '
                SELECT
                    participants.`CODE`
                FROM
                    procedures_participants
                INNER JOIN participants
                    ON procedures_participants.`PARTICIPANT` = participants.`ID`
                INNER JOIN procedures
                    ON procedures_participants.`PROCEDURE` = procedures.`ID`
                WHERE
                    procedures.`CODE` = ?';

            return $this->getQueryResult($sqlQuery, [$this->getCode()]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure fields
     *
     * @param   array   $participantsCodeArray      procedure participants code array
     * @return  array                               procedure fields query result
     * @example
     * [
     *      [
     *          'ID'                    => procedureFieldId
     *          'PARTICIPANTS_FIELDS'   =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *      ],
     *      [
     *          'ID'                    => procedureFieldId
     *          'PARTICIPANTS_FIELDS'   =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureFields(array $participantsCodeArray) : array
    {
        try
        {
            $result                         = [];
            $procedureFieldsIdArray         = [];
            $procedureFieldsIdQueryResult   = $this->queryProcedureFieldsId();

            foreach ($procedureFieldsIdQueryResult as $item)
            {
                $procedureFieldsIdArray[] = $item['ID'];
            }

            $procedureParticipantsFieldsQueryResult = $this->queryProcedureParticipantsFields($participantsCodeArray, $procedureFieldsIdArray);
            foreach ($procedureParticipantsFieldsQueryResult as $item)
            {
                $procedureFieldId       = $item['PROCEDURE_FIELD_ID'];
                $participantCode        = $item['PARTICIPANT_CODE'];
                $participantFieldName   = $item['PARTICIPANT_FIELD_NAME'];

                if (!array_key_exists($procedureFieldId, $result))
                {
                    $result[$procedureFieldId] =
                        [
                            'ID'                    => $procedureFieldId,
                            'PARTICIPANTS_FIELDS'   => []
                        ];
                }

                $result[$procedureFieldId]['PARTICIPANTS_FIELDS'][$participantCode] = $participantFieldName;
            }

            return array_values($result);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure fields id
     *
     * @return  array                               procedure fields id query result
     * @example
     * [
     *      ['ID' => procedureFieldId],
     *      ['ID' => procedureFieldId]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureFieldsId() : array
    {
        try
        {
            $sqlQuery = '
                SELECT
                    procedures_fields.`ID`
                FROM
                    procedures_fields
                INNER JOIN procedures
                    ON procedures_fields.`PROCEDURE` = procedures.`ID`
                WHERE
                    procedures.`CODE` = ?';

            return $this->getQueryResult($sqlQuery, [$this->getCode()]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure participants fields
     *
     * @param   array   $participantsCodeArray      procedure participants code array
     * @param   array   $procedureFieldsIdArray     procedure fields id array
     * @return  array                               procedure participants fields query result
     * @example
     * [
     *      [
     *          'PARTICIPANT_CODE'          => participantCode,
     *          'PROCEDURE_FIELD_ID'        => procedureFieldId,
     *          'PARTICIPANT_FIELD_NAME'    => participantFieldName
     *      ],
     *      [
     *          'PARTICIPANT_CODE'          => participantCode,
     *          'PROCEDURE_FIELD_ID'        => procedureFieldId,
     *          'PARTICIPANT_FIELD_NAME'    => participantFieldName
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureParticipantsFields(array $participantsCodeArray, array $procedureFieldsIdArray) : array
    {
        if (count($participantsCodeArray) <= 0 || count($procedureFieldsIdArray) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams                = array_merge($procedureFieldsIdArray, $participantsCodeArray);
            $fieldsPlaceholder          = rtrim(str_repeat('?, ', count($procedureFieldsIdArray)), ', ');
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participantsCodeArray)), ', ');
            $sqlQuery                   = "
                SELECT
                    participants.`CODE`                               AS PARTICIPANT_CODE,
                    procedures_participants_fields.`PROCEDURE_FIELD`  AS PROCEDURE_FIELD_ID,
                    participants_fields.`NAME`                        AS PARTICIPANT_FIELD_NAME
                FROM
                    procedures_participants_fields
                INNER JOIN participants_fields
                    ON procedures_participants_fields.`PARTICIPANT_FIELD` = participants_fields.`ID`
                INNER JOIN participants
                    ON participants_fields.`PARTICIPANT` = participants.`ID`
                WHERE
                    procedures_participants_fields.`PROCEDURE_FIELD`  IN  ($fieldsPlaceholder) AND
                    participants.`CODE`                               IN  ($participantsPlaceholder)";

            return $this->getQueryResult($sqlQuery, $queryParams);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data matching rules
     *
     * @param   array   $participantsCodeArray      procedure participants code array
     * @param   array   $procedureFieldsIdArray     procedure fields id array
     * @return  array                               procedure data matching rules query result
     * @example
     * [
     *      [
     *          'ID'                => ruleId,
     *          'PARTICIPANTS'      => [participantCode, participantCode],
     *          'PROCEDURE_FIELDS'  => [procedureFieldId, procedureFieldId]
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRules(array $participantsCodeArray, array $procedureFieldsIdArray) : array
    {
        try
        {
            $result             = [];
            $rulesId            = [];
            $rulesQueryResult   = $this->queryProcedureDataMatchingRulesId();

            foreach ($rulesQueryResult as $item)
            {
                $ruleId             = $item['ID'];
                $rulesId[]          = $ruleId;
                $result[$ruleId]    =
                    [
                        'ID'                => $ruleId,
                        'PARTICIPANTS'      => [],
                        'PROCEDURE_FIELDS'  => []
                    ];
            }

            $rulesParticipantsQueryResult = $this->queryProcedureDataMatchingRulesParticipants($rulesId, $participantsCodeArray);
            foreach ($rulesParticipantsQueryResult as $item)
            {
                $result[$item['RULE']]['PARTICIPANTS'][] = $item['PARTICIPANT_CODE'];
            }

            $rulesFieldsQueryResult = $this->queryProcedureDataMatchingRulesProcedureFields($rulesId, $procedureFieldsIdArray);
            foreach ($rulesFieldsQueryResult as $item)
            {
                $result[$item['RULE']]['PROCEDURE_FIELDS'][] = $item['PROCEDURE_FIELD'];
            }

            return array_values($result);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data matching rules id
     *
     * @return  array                               procedure data matching rules id query result
     * @example
     * [
     *      ['ID' => dataMatchingRuleId],
     *      ['ID' => dataMatchingRuleId]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesId() : array
    {
        try
        {
            $sqlQuery = '
                SELECT
                    procedures_data_matching_rules.`ID`
                FROM
                    procedures_data_matching_rules
                INNER JOIN procedures
                    ON procedures_data_matching_rules.`PROCEDURE` = procedures.`ID`
                WHERE
                    procedures.`CODE` = ?';

            return $this->getQueryResult($sqlQuery, [$this->getCode()]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data matching rules participants
     *
     * @param   array   $procedureRulesIdArray      procedure rules id array
     * @param   array   $participantsCodeArray      procedure participants code array
     * @return  array                               procedure data matching rules participants query result
     * @example
     * [
     *      [
     *          'RULE'              => matchingRuleId,
     *          'PARTICIPANT_CODE'  => participantCode
     *      ],
     *      [
     *          'RULE'              => matchingRuleId,
     *          'PARTICIPANT_CODE'  => participantCode
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesParticipants(array $procedureRulesIdArray, array $participantsCodeArray) : array
    {
        if (count($procedureRulesIdArray) <= 0 || count($participantsCodeArray) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams                = array_merge($procedureRulesIdArray, $participantsCodeArray);
            $rulesPlaceholder           = rtrim(str_repeat('?, ', count($procedureRulesIdArray)), ', ');
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participantsCodeArray)), ', ');
            $sqlQuery                   = "
                SELECT
                    procedures_data_matching_rules_participants.`RULE`,
                    participants.`CODE` AS PARTICIPANT_CODE
                FROM
                    procedures_data_matching_rules_participants
                INNER JOIN participants
                    ON procedures_data_matching_rules_participants.`PARTICIPANT` = participants.`ID`
                WHERE
                    procedures_data_matching_rules_participants.`RULE`  IN  ($rulesPlaceholder) AND
                    participants.`CODE`                                 IN  ($participantsPlaceholder)";

            return $this->getQueryResult($sqlQuery, $queryParams);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data matching rules fields
     *
     * @param   array   $procedureRulesIdArray      procedure rules id array
     * @param   array   $procedureFieldsIdArray     procedure fields id array
     * @return  array                               procedure data matching rules fields query result
     * @example
     * [
     *      [
     *          'RULE'              => matchingRuleId,
     *          'PROCEDURE_FIELD'   => procedureFieldId
     *      ],
     *      [
     *          'RULE'              => matchingRuleId,
     *          'PROCEDURE_FIELD'   => procedureFieldId
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesProcedureFields(array $procedureRulesIdArray, array $procedureFieldsIdArray) : array
    {
        if (count($procedureRulesIdArray) <= 0 || count($procedureFieldsIdArray) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams        = array_merge($procedureRulesIdArray, $procedureFieldsIdArray);
            $rulesPlaceholder   = rtrim(str_repeat('?, ', count($procedureRulesIdArray)), ', ');
            $fieldsPlaceholder  = rtrim(str_repeat('?, ', count($procedureFieldsIdArray)), ', ');
            $sqlQuery           = "
                SELECT
                    procedures_data_matching_rules_fields.`RULE`,
                    procedures_data_matching_rules_fields.`PROCEDURE_FIELD`
                FROM
                    procedures_data_matching_rules_fields
                WHERE
                    procedures_data_matching_rules_fields.`RULE`            IN  ($rulesPlaceholder) AND
                    procedures_data_matching_rules_fields.`PROCEDURE_FIELD` IN  ($fieldsPlaceholder)";

            return $this->getQueryResult($sqlQuery, $queryParams);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data combining rules
     *
     * @param   array   $participantsCodeArray      procedure participants code array
     * @return  array                               procedure data combining rules query result
     * @example
     * [
     *      [
     *          'PARTICIPANT_CODE'  => participantCode,
     *          'FIELD_NAME'        => participantFieldName,
     *          'WEIGHT'            => participantFieldWeight
     *      ],
     *      [
     *          'PARTICIPANT_CODE'  => participantCode,
     *          'FIELD_NAME'        => participantFieldName,
     *          'WEIGHT'            => participantFieldWeight
     *      ]
     * ]
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function queryProcedureDataCombiningRules(array $participantsCodeArray) : array
    {
        if (count($participantsCodeArray) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams                = array_merge([$this->getCode()], $participantsCodeArray);
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participantsCodeArray)), ', ');
            $sqlQuery                   = "
                SELECT
                    procedures_data_combining_rules.`WEIGHT`,
                    participants.`CODE`         AS PARTICIPANT_CODE,
                    participants_fields.`NAME`  AS FIELD_NAME
                FROM
                    procedures_data_combining_rules
                INNER JOIN procedures
                    ON procedures_data_combining_rules.`PROCEDURE` = procedures.`ID`
                INNER JOIN participants_fields
                    ON procedures_data_combining_rules.`PARTICIPANT_FIELD` = participants_fields.`ID`
                INNER JOIN participants
                    ON participants_fields.`PARTICIPANT` = participants.`ID`
                WHERE
                    procedures.`CODE`   =   ? AND
                    participants.`CODE` IN  ($participantsPlaceholder)";

            return $this->getQueryResult($sqlQuery, $queryParams);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get query result as array
     *
     * @param   string  $sqlQuery                   SQL query
     * @param   array   $params                     query params
     * @return  array                               query result as array
     * @throws  RuntimeException                    query process error
     ************************************************************************/
    private function getQueryResult(string $sqlQuery, array $params = []) : array
    {
        try
        {
            $result         = [];
            $queryResult    = DB::getInstance()->query($sqlQuery, $params);

            while (!$queryResult->isEmpty())
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
     * create participant by code
     *
     * @param   string $code                        participant code
     * @return  Participant                         participant
     * @throws  UnknownParticipantException         creating participant error
     ************************************************************************/
    private function createParticipant(string $code) : Participant
    {
        $reflection = new ReflectionClass(Participant::class);
        $namespace  = $reflection->getNamespaceName();
        $className  = $namespace.'\\'.$code;

        try
        {
            return new $className;
        }
        catch (Throwable $exception)
        {
            $exceptionToThrow = new UnknownParticipantException;
            $exceptionToThrow->setParticipantCode($code);
            throw $exceptionToThrow;
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
        $code           = $this->getCode();
        $fullMessage    = "Procedure \"$code\": $message";

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