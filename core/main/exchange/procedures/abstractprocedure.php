<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    DomainException,
    RuntimeException,
    InvalidArgumentException,
    ReflectionClass,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\ParticipantField,
    Main\Exchange\Procedures\Fields\ProcedureField,
    Main\Exchange\Procedures\Fields\FieldsSet,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Procedures\Rules\DataCombiningRules;
/** ***********************************************************************************************
 * Application procedure abstract class
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractProcedure implements Procedure
{
    private
        $participants       = [],
        $participantsFields = [],
        $procedureFields    = [],
        $dataMatchingRules  = [],
        $dataCombiningRules = [];
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        $this->participants         = $this->createParticipantsCollection();
        $this->participantsFields   = $this->createParticipantsFieldsCollection($this->participants);
        $this->procedureFields      = $this->createProcedureFieldsCollection($this->participantsFields);
        $this->dataMatchingRules    = $this->createDataMatchingRulesCollection($this->participants, $this->procedureFields);
        $this->dataCombiningRules   = $this->createDataCombiningRulesCollection($this->participants);
    }
    /** **********************************************************************
     * get procedure participants
     *
     * @return  ParticipantsSet                             procedure participants set
     ************************************************************************/
    final public function getParticipants() : ParticipantsSet
    {
        $result         = new ParticipantsSet;
        $logger         = Logger::getInstance();
        $procedureName  = static::class;

        if (count($this->participants) <= 0)
        {
            $logger->addWarning("Procedure \"$procedureName\" has no participants");
            return $result;
        }

        try
        {
            foreach ($this->participants as $participant)
            {
                $result->push($participant);
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Error on filling procedure \"$procedureName\" participants set: $error");
        }

        $logger->addNotice("Procedure \"$procedureName\" participants set constructed and returned");
        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * get procedure fields
     *
     * @return  FieldsSet                                   procedure fields set
     ************************************************************************/
    final public function getFields() : FieldsSet
    {
        $result         = new FieldsSet;
        $logger         = Logger::getInstance();
        $procedureName  = static::class;

        if (count($this->procedureFields) <= 0)
        {
            $logger->addWarning("Procedure \"$procedureName\" has no fields");
            return $result;
        }

        try
        {
            foreach ($this->procedureFields as $field)
            {
                $field->rewind();
                $result->push($field);
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Error on filling procedure \"$procedureName\" fields set: $error");
        }

        $logger->addNotice("Procedure \"$procedureName\" fields set constructed and returned");
        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * get procedure data matching rules
     *
     * @return  DataMatchingRules                           procedure data matching rules set
     ************************************************************************/
    final public function getDataMatchingRules() : DataMatchingRules
    {
        $result         = new DataMatchingRules;
        $logger         = Logger::getInstance();
        $procedureName  = static::class;

        if (count($this->dataMatchingRules) <= 0)
        {
            $logger->addWarning("Procedure \"$procedureName\" has no data matching rules");
            return $result;
        }

        try
        {
            foreach ($this->dataMatchingRules as $rule)
            {
                $participantsSet    = new ParticipantsSet;
                $fieldsSet          = new FieldsSet;

                foreach ($rule['participants'] as $participantName)
                {
                    $participantsSet->push($this->participants[$participantName]);
                }
                foreach ($rule['fields'] as $procedureFieldId)
                {
                    $procedureField = $this->procedureFields[$procedureFieldId];
                    $procedureField->rewind();
                    $fieldsSet->push($procedureField);
                }

                $participantsSet->rewind();
                $fieldsSet->rewind();
                $result->set($participantsSet, $fieldsSet);
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Error on filling procedure \"$procedureName\" data matching rules set: $error");
        }

        $logger->addNotice("Procedure \"$procedureName\" data matching rules set constructed and returned");
        return $result;
    }
    /** **********************************************************************
     * get data combining rules
     *
     * @return  DataCombiningRules                          procedure data combining rules
     ************************************************************************/
    final public function getDataCombiningRules() : DataCombiningRules
    {
        $result         = new DataCombiningRules;
        $logger         = Logger::getInstance();
        $procedureName  = static::class;

        if (count($this->dataCombiningRules) <= 0)
        {
            $logger->addWarning("Procedure \"$procedureName\" has no data combining rules");
            return $result;
        }

        try
        {
            foreach ($this->dataCombiningRules as $participantName => $participantFields)
            {
                foreach ($participantFields as $fieldName => $weight)
                {
                    $result->set($this->participantsFields[$participantName][$fieldName], $weight);
                }
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Error on filling procedure \"$procedureName\" data combining rules set: $error");
        }

        $logger->addNotice("Procedure \"$procedureName\" data combining rules set constructed and returned");
        return $result;
    }
    /** **********************************************************************
     * create procedure participants collection
     *
     * @return  array                                       procedure participants collection
     * @example
     * [
     *      participantName => participant,
     *      participantName => participant
     * ]
     ************************************************************************/
    private function createParticipantsCollection() : array
    {
        $logger         = Logger::getInstance();
        $procedureName  = static::class;
        $result         = [];

        try
        {
            $queryResult = $this->queryProcedureParticipants();
            foreach ($queryResult as $item)
            {
                $result[$item['NAME']] = $this->createParticipant($item['NAME']);
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to query participants for procedure \"$procedureName\": $error");
        }
        catch (DomainException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to create participant for procedure \"$procedureName\": $error");
        }

        return $result;
    }
    /** **********************************************************************
     * create participants fields collection
     *
     * @param   Participant[]   $participantsCollection     participants
     * @return  array                                       procedure participants fields collection
     * @example
     * [
     *      participantName =>
     *      [
     *          fieldName   => procedureParticipantField,
     *          fieldName   => procedureParticipantField
     *      ],
     *      participantName =>
     *      [
     *          fieldName   => procedureParticipantField,
     *          fieldName   => procedureParticipantField
     *      ]
     * ]
     ************************************************************************/
    private function createParticipantsFieldsCollection(array $participantsCollection) : array
    {
        $logger         = Logger::getInstance();
        $procedureName  = static::class;
        $result         = [];

        try
        {
            foreach ($participantsCollection as $participantName => $participant)
            {
                $participantFields          = $participant->getFields();
                $result[$participantName]   = [];

                while ($participantFields->valid())
                {
                    $field              = $participantFields->current();
                    $fieldName          = $field->getParam('name');
                    $participantField   = new ParticipantField($participant, $field);

                    $participantFields->next();
                    $result[$participantName][$fieldName] = $participantField;
                }
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to create procedure \"$procedureName\" participant field: $error");
        }

        return $result;
    }
    /** **********************************************************************
     * create procedure fields collection
     *
     * @param   array   $participantsFieldsCollection       procedure participants fields collection
     * @return  array                                       procedure fields collection
     * @example
     * [
     *      fieldId => field,
     *      fieldId => field
     * ]
     ************************************************************************/
    private function createProcedureFieldsCollection(array $participantsFieldsCollection) : array
    {
        $logger         = Logger::getInstance();
        $procedureName  = static::class;
        $result         = [];

        try
        {
            $queryResult = $this->queryProcedureFields(array_keys($participantsFieldsCollection));
            foreach ($queryResult as $item)
            {
                $procedureField     = new ProcedureField;
                $procedureFieldId   = $item['ID'];

                foreach ($item['PARTICIPANTS_FIELDS'] as $participantName => $fieldName)
                {
                    $participantField = $participantsFieldsCollection[$participantName][$fieldName];
                    $procedureField->push($participantField);
                }

                if ($procedureField->count() <= 0)
                {
                    $logger->addWarning("Procedure field \"$procedureFieldId\" has no participants fields");
                }

                $procedureField->rewind();
                $result[$procedureFieldId] = $procedureField;
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to query fields for procedure \"$procedureName\": $error");
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to create procedure \"$procedureName\" field: $error");
        }

        return $result;
    }
    /** **********************************************************************
     * get procedure data matching rules collection
     *
     * @param   array   $participantsCollection             procedure participants collection
     * @param   array   $procedureFieldsCollection          procedure fields collection
     * @return  array                                       procedure data matching rules collection
     * @example
     * [
     *      ruleId  =>
     *      [
     *          'participants'  => [participantName, participantName, participantName],
     *          'fields'        => [procedureFieldId, procedureFieldId, procedureFieldId]
     *      ]
     * ]
     ************************************************************************/
    private function createDataMatchingRulesCollection(array $participantsCollection, array $procedureFieldsCollection) : array
    {
        $logger         = Logger::getInstance();
        $procedureName  = static::class;
        $result         = [];

        try
        {
            $rulesQuery = $this->queryProcedureDataMatchingRules(array_keys($participantsCollection), array_keys($procedureFieldsCollection));
            foreach ($rulesQuery as $index => $rule)
            {
                $ruleId             = $rule['ID'];
                $result[$ruleId]    =
                [
                    'participants'  => $rule['PARTICIPANTS'],
                    'fields'        => $rule['PROCEDURE_FIELDS']
                ];

                if (count($result[$ruleId]['participants']) <= 0)
                {
                    $logger->addWarning("Procedure matching rule \"$ruleId\" has no participants");
                }
                if (count($result[$ruleId]['fields']) <= 0)
                {
                    $logger->addWarning("Procedure matching rule \"$ruleId\" has no participants fields");
                }
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to query data matching rules for procedure \"$procedureName\": $error");
        }

        return $result;
    }
    /** **********************************************************************
     * get procedure data combining rules collection
     *
     * @param   array   $participantsCollection             procedure participants fields collection
     * @return  array                                       procedure data combining rules collection
     * @example
     * [
     *      participantName =>
     *      [
     *          fieldName   => weight,
     *          fieldName   => weight
     *      ],
     *      participantName =>
     *      [
     *          fieldName   => weight,
     *          fieldName   => weight
     *      ]
     * ]
     ************************************************************************/
    private function createDataCombiningRulesCollection(array $participantsCollection) : array
    {
        $logger         = Logger::getInstance();
        $procedureName  = static::class;
        $result         = [];

        try
        {
            $rulesQuery = $this->queryProcedureDataCombiningRules(array_keys($participantsCollection));
            foreach ($rulesQuery as $index => $rule)
            {
                $participantName    = $rule['PARTICIPANT_NAME'];
                $fieldName          = $rule['FIELD_NAME'];
                $weight             = (int) $rule['WEIGHT'];

                if (!array_key_exists($participantName, $result))
                {
                    $result[$participantName] = [];
                }

                $result[$participantName][$fieldName] = $weight;
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to query data combining rules for procedure \"$procedureName\": $error");
        }

        return $result;
    }
    /** **********************************************************************
     * query procedure participants
     *
     * @return  array                                       procedure participant query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureParticipants() : array
    {
        try
        {
            $reflection     = new ReflectionClass(static::class);
            $procedureName  = $reflection->getShortName();
            $sqlQuery       = '
            SELECT
                participants.`NAME`
            FROM
                procedures_participants
            INNER JOIN participants
                ON procedures_participants.`PARTICIPANT` = participants.`ID`
            INNER JOIN procedures
                ON procedures_participants.`PROCEDURE` = procedures.`ID`
            WHERE
                procedures.`NAME` = ?';

            return $this->getQueryResult($sqlQuery, [$procedureName]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure fields
     *
     * @param   array   $participants                       procedure participants
     * @return  array                                       procedure fields query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureFields(array $participants) : array
    {
        try
        {
            $procedureFieldsQuery   = $this->queryProcedureFieldsId();
            $procedureFieldsId      = [];
            $result                 = [];

            foreach ($procedureFieldsQuery as $item)
            {
                $procedureFieldsId[] = $item['ID'];
            }

            $procedureParticipantsFieldsQuery = $this->queryProcedureParticipantsFields($participants, $procedureFieldsId);
            foreach ($procedureParticipantsFieldsQuery as $item)
            {
                $procedureFieldId       = $item['PROCEDURE_FIELD_ID'];
                $participantName        = $item['PARTICIPANT_NAME'];
                $participantFieldName   = $item['PARTICIPANT_FIELD_NAME'];

                if (!array_key_exists($procedureFieldId, $result))
                {
                    $result[$procedureFieldId] =
                    [
                        'ID'                    => $procedureFieldId,
                        'PARTICIPANTS_FIELDS'   => []
                    ];
                }

                $result[$procedureFieldId]['PARTICIPANTS_FIELDS'][$participantName] = $participantFieldName;
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
     * @return  array                                       procedure fields ID query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureFieldsId() : array
    {
        try
        {
            $reflection     = new ReflectionClass(static::class);
            $procedureName  = $reflection->getShortName();
            $sqlQuery       = '
            SELECT
                procedures_fields.`ID`
            FROM
                procedures_fields
            INNER JOIN procedures
                ON procedures_fields.`PROCEDURE` = procedures.`ID`
            WHERE
                procedures.`NAME` = ?';

            return $this->getQueryResult($sqlQuery, [$procedureName]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure participants fields
     *
     * @param   array   $participants                       procedure participants
     * @param   array   $procedureFields                    procedure fields
     * @return  array                                       procedure participants fields query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureParticipantsFields(array $participants, array $procedureFields) : array
    {
        if (count($participants) <= 0 || count($procedureFields) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams                = array_merge($procedureFields, $participants);
            $fieldsPlaceholder          = rtrim(str_repeat('?, ', count($procedureFields)), ', ');
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participants)), ', ');
            $sqlQuery                   = "
            SELECT
                participants.`NAME`                               AS PARTICIPANT_NAME,
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
                participants.`NAME`                               IN  ($participantsPlaceholder)";

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
     * @param   array   $participants                       procedure participants
     * @param   array   $procedureFields                    procedure fields
     * @return  array                                       procedure data matching rules query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRules(array $participants, array $procedureFields) : array
    {
        try
        {
            $rulesQuery = $this->queryProcedureDataMatchingRulesId();
            $rulesId    = [];
            $result     = [];

            foreach ($rulesQuery as $item)
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

            $rulesParticipantsQuery = $this->queryProcedureDataMatchingRulesParticipants($rulesId, $participants);
            foreach ($rulesParticipantsQuery as $item)
            {
                $result[$item['RULE']]['PARTICIPANTS'][] = $item['PARTICIPANT_NAME'];
            }

            $rulesFieldsQuery = $this->queryProcedureDataMatchingRulesProcedureFields($rulesId, $procedureFields);
            foreach ($rulesFieldsQuery as $item)
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
     * @return  array                                       procedure data matching rules ID query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesId() : array
    {
        try
        {
            $reflection     = new ReflectionClass(static::class);
            $procedureName  = $reflection->getShortName();
            $sqlQuery       = '
            SELECT
                procedures_data_matching_rules.`ID`
            FROM
                procedures_data_matching_rules
            INNER JOIN procedures
                ON procedures_data_matching_rules.`PROCEDURE` = procedures.`ID`
            WHERE
                procedures.`NAME` = ?';

            return $this->getQueryResult($sqlQuery, [$procedureName]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * query procedure data matching rules participants
     *
     * @param   array   $procedureRules                     procedure rules
     * @param   array   $participants                       procedure participants
     * @return  array                                       procedure data matching rules participants query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesParticipants(array $procedureRules, array $participants) : array
    {
        if (count($procedureRules) <= 0 || count($participants) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams                = array_merge($procedureRules, $participants);
            $rulesPlaceholder           = rtrim(str_repeat('?, ', count($procedureRules)), ', ');
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participants)), ', ');
            $sqlQuery                   = "
            SELECT
                procedures_data_matching_rules_participants.`RULE`,
                participants.`NAME` AS PARTICIPANT_NAME
            FROM
                procedures_data_matching_rules_participants
            INNER JOIN participants
                ON procedures_data_matching_rules_participants.`PARTICIPANT` = participants.`ID`
            WHERE
                procedures_data_matching_rules_participants.`RULE`  IN  ($rulesPlaceholder) AND
                participants.`NAME`                                 IN  ($participantsPlaceholder)";

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
     * @param   array   $procedureRules                     procedure rules
     * @param   array   $procedureFields                    procedure fields
     * @return  array                                       procedure data matching rules fields query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureDataMatchingRulesProcedureFields(array $procedureRules, array $procedureFields) : array
    {
        if (count($procedureRules) <= 0 || count($procedureFields) <= 0)
        {
            return [];
        }

        try
        {
            $queryParams        = array_merge($procedureRules, $procedureFields);
            $rulesPlaceholder   = rtrim(str_repeat('?, ', count($procedureRules)), ', ');
            $fieldsPlaceholder  = rtrim(str_repeat('?, ', count($procedureFields)), ', ');
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
     * @param   array   $participants                       procedure participants
     * @return  array                                       procedure data combining rules query result
     * @throws  RuntimeException                            query process error
     ************************************************************************/
    private function queryProcedureDataCombiningRules(array $participants) : array
    {
        if (count($participants) <= 0)
        {
            return [];
        }

        try
        {
            $reflection                 = new ReflectionClass(static::class);
            $procedureName              = $reflection->getShortName();
            $queryParams                = array_merge([$procedureName], $participants);
            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participants)), ', ');
            $sqlQuery                   = "
            SELECT
                procedures_data_combining_rules.`WEIGHT`,
                participants.`NAME`         AS PARTICIPANT_NAME,
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
                procedures.`NAME`   =   ? AND
                participants.`NAME` IN  ($participantsPlaceholder)";

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
     * @param   string  $sqlQuery                           SQL query
     * @param   array   $params                             query params
     * @return  array                                       query result as array
     * @throws  RuntimeException                            query process error
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
     * create participant by name
     *
     * @param   string  $name                               participant name
     * @return  Participant                                 participant
     * @throws  DomainException                             creating participant error
     ************************************************************************/
    private function createParticipant(string $name) : Participant
    {
        $classReflection    = new ReflectionClass(Participant::class);
        $classNamespace     = $classReflection->getNamespaceName();
        $classFullName      = $classNamespace.'\\'.$name;

        try
        {
            return new $classFullName;
        }
        catch (Throwable $exception)
        {
            throw new DomainException("creating participant \"$classFullName\" error");
        }
    }
}