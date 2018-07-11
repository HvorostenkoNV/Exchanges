<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    RuntimeException,
    InvalidArgumentException,
    ReflectionException,
    ReflectionClass,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Fields\Field     as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet as ParticipantFieldsSet,
    Main\Exchange\Participants\Exceptions\UnknownParticipantException,
    Main\Exchange\Participants\Exceptions\UnknownParticipantFieldException,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\Field       as ProcedureField,
    Main\Exchange\Procedures\Fields\FieldsSet   as ProcedureFieldsSet,
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
            $this->code = static::class;
        }

        $this->addLogMessage('created', 'notice');

        $this->participantsCollection = $this->constructParticipantsCollection();
        if ($this->participantsCollection->count() <= 0)
        {
            $this->addLogMessage('participants collection is empty', 'warning');
        }

        $this->participantsFieldsCollection = $this->constructParticipantsFieldsCollection();
        if ($this->participantsFieldsCollection->count() <= 0)
        {
            $this->addLogMessage('participants fields collection is empty', 'warning');
        }

        $this->procedureFieldsCollection = $this->constructProcedureFieldsCollection();
        if ($this->procedureFieldsCollection->count() <= 0)
        {
            $this->addLogMessage('procedure fields collection is empty', 'warning');
        }

        $this->dataMatchingRulesCollection = $this->constructDataMatchingRulesCollection();
        if ($this->dataMatchingRulesCollection->count() <= 0)
        {
            $this->addLogMessage('data matching rules collection is empty', 'warning');
        }

        $this->dataCombiningRulesCollection = $this->constructDataCombiningRulesCollection();
        if ($this->dataCombiningRulesCollection->count() <= 0)
        {
            $this->addLogMessage('data combining rules collection is empty', 'warning');
        }
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
        $this->participantsCollection->rewind();
        return $this->participantsCollection;
    }
    /** **********************************************************************
     * get procedure fields set
     *
     * @return  ProcedureFieldsSet                  procedure fields set
     ************************************************************************/
    final public function getFields() : ProcedureFieldsSet
    {
        $this->procedureFieldsCollection->rewind();
        return $this->procedureFieldsCollection;
    }
    /** **********************************************************************
     * get procedure data matching rules
     *
     * @return  DataMatchingRules                   procedure data matching rules
     ************************************************************************/
    final public function getDataMatchingRules() : DataMatchingRules
    {
        return $this->dataMatchingRulesCollection;
    }
    /** **********************************************************************
     * get data combining rules
     *
     * @return  DataCombiningRules                  procedure data combining rules
     ************************************************************************/
    final public function getDataCombiningRules() : DataCombiningRules
    {
        return $this->dataCombiningRulesCollection;
    }
    /** **********************************************************************
     * construct participants collection
     *
     * @return  ParticipantsSet                     participants collection
     ************************************************************************/
    private function constructParticipantsCollection() : ParticipantsSet
    {
        $result         = new ParticipantsSet;
        $queryResult    = null;

        try
        {
            $queryResult = $this->queryParticipants();
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("participants query failed, \"$error\"", 'warning');
            return $result;
        }

        foreach ($queryResult as $item)
        {
            try
            {
                $participant = $this->createParticipant($item['CODE']);
                $result->push($participant);
            }
            catch (UnknownParticipantException $exception)
            {
                $participantCode = $exception->getParticipantCode();
                $this->addLogMessage("failed to create participant \"$participantCode\"", 'warning');
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing participants collection, \"$error\"", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * construct participants fields collection
     *
     * @return  MapData                             participants fields collection
     ************************************************************************/
    private function constructParticipantsFieldsCollection() : MapData
    {
        $result = new MapData;

        $this->participantsCollection->rewind();
        while ($this->participantsCollection->valid())
        {
            $participant            = $this->participantsCollection->current();
            $participantFieldsSet   = $participant->getFields();

            $result->set($participant, $participantFieldsSet);
            $this->participantsCollection->next();
        }

        return $result;
    }
    /** **********************************************************************
     * construct procedure fields collection
     *
     * @return  ProcedureFieldsSet                  procedure fields collection
     ************************************************************************/
    private function constructProcedureFieldsCollection() : ProcedureFieldsSet
    {
        $result         = new ProcedureFieldsSet;
        $queryResult    = null;

        try
        {
            $participantsCodeArray = [];

            $this->participantsCollection->rewind();
            while ($this->participantsCollection->valid())
            {
                $participantsCodeArray[] = $this->participantsCollection->current()->getCode();
                $this->participantsCollection->next();
            }

            $queryResult = $this->queryProcedureFields($participantsCodeArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("procedure fields query failed, \"$error\"", 'warning');
            return $result;
        }

        foreach ($queryResult as $item)
        {
            $procedureFieldParams   = new MapData;
            $participantsFieldsSet  = new ParticipantFieldsSet;
            $procedureFieldId       = (int) $item['ID'];

            $procedureFieldParams->set('id', $procedureFieldId);

            foreach ($item['PARTICIPANTS_FIELDS'] as $participantCode => $participantFieldName)
            {
                try
                {
                    $participantField = $this->findParticipantField($participantCode, $participantFieldName);
                    $participantsFieldsSet->push($participantField);
                }
                catch (UnknownParticipantFieldException $exception)
                {
                    $this->addLogMessage("caught unknown participant field\"$participantFieldName\" of participant \"$participantCode\" in procedure field \"$procedureFieldId\"", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing procedure fields collection, \"$error\"", 'warning');
                }
            }

            try
            {
                $procedureField = new ProcedureField($this, $procedureFieldParams, $participantsFieldsSet);
                $result->push($procedureField);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing procedure fields collection, \"$error\"", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * construct data matching rules collection
     *
     * @return  DataMatchingRules                   data matching rules collection
     ************************************************************************/
    private function constructDataMatchingRulesCollection() : DataMatchingRules
    {
        $result         = new DataMatchingRules;
        $queryResult    = null;

        try
        {
            $participantsCodeArray  = [];
            $procedureFieldsIdArray = [];

            $this->participantsCollection->rewind();
            while ($this->participantsCollection->valid())
            {
                $participantsCodeArray[] = $this->participantsCollection->current()->getCode();
                $this->participantsCollection->next();
            }
            $this->procedureFieldsCollection->rewind();
            while ($this->procedureFieldsCollection->valid())
            {
                $procedureFieldsIdArray[] = $this->procedureFieldsCollection->current()->getParam('id');
                $this->procedureFieldsCollection->next();
            }

            $queryResult = $this->queryProcedureDataMatchingRules($participantsCodeArray, $procedureFieldsIdArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("data matching rules query failed, \"$error\"", 'warning');
            return $result;
        }

        foreach ($queryResult as $item)
        {
            $participantsSet    = new ParticipantsSet;
            $procedureFieldsSet = new ProcedureFieldsSet;
            $ruleId             = (int) $item['ID'];

            foreach ($item['PARTICIPANTS'] as $participantCode)
            {
                try
                {
                    $participant = $this->findParticipant($participantCode);
                    $participantsSet->push($participant);
                }
                catch (UnknownParticipantException $exception)
                {
                    $this->addLogMessage("unknown participant \"$participantCode\" on constructing data matching rules collection", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing data matching rules collection, \"$error\"", 'warning');
                }
            }
            foreach ($item['PROCEDURE_FIELDS'] as $procedureFieldId)
            {
                try
                {
                    $procedureField = $this->findProcedureField((int) $procedureFieldId);
                    $procedureFieldsSet->push($procedureField);
                }
                catch (UnknownProcedureFieldException $exception)
                {
                    $this->addLogMessage("unknown procedure field \"$procedureFieldId\" on constructing data matching rules collection", 'warning');
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $this->addLogMessage("unexpected error on constructing data matching rules collection, \"$error\"", 'warning');
                }
            }

            if ($participantsSet->count() <= 0)
            {
                $this->addLogMessage("caught data matching rule \"$ruleId\" with no participants", 'warning');
                continue;
            }
            if ($procedureFieldsSet->count() <= 0)
            {
                $this->addLogMessage("caught data matching rule \"$ruleId\" with no procedure fields", 'warning');
                continue;
            }

            try
            {
                $result->set($participantsSet, $procedureFieldsSet);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing data matching rules collection, \"$error\"", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * construct data combining rules collection
     *
     * @return  DataCombiningRules                  data combining rules collection
     ************************************************************************/
    private function constructDataCombiningRulesCollection() : DataCombiningRules
    {
        $result         = new DataCombiningRules;
        $queryResult    = null;

        try
        {
            $participantsCodeArray = [];

            $this->participantsCollection->rewind();
            while ($this->participantsCollection->valid())
            {
                $participantsCodeArray[] = $this->participantsCollection->current()->getCode();
                $this->participantsCollection->next();
            }

            $queryResult = $this->queryProcedureDataCombiningRules($participantsCodeArray);
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("data combining rules query failed, \"$error\"", 'warning');
        }

        foreach ($queryResult as $item)
        {
            $participantCode        = $item['PARTICIPANT_CODE'];
            $participantFieldName   = $item['FIELD_NAME'];
            $weight                 = (int) $item['WEIGHT'];

            try
            {
                $participantField = $this->findParticipantField($participantCode, $participantFieldName);
                $result->set($participantField, $weight);
            }
            catch (UnknownParticipantFieldException $exception)
            {
                $this->addLogMessage("unknown participant field \"$participantFieldName\" of participant \"$participantCode\" on constructing data combining rules collection", 'warning');
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing data matching rules collection, \"$error\"", 'warning');
            }
        }

        return $result;
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
        $this->participantsCollection->rewind();
        while ($this->participantsCollection->valid())
        {
            $participant = $this->participantsCollection->current();
            if ($participant->getCode() == $participantCode)
            {
                return $participant;
            }
            $this->participantsCollection->next();
        }

        $exception = new UnknownParticipantException;
        $exception->setParticipantCode($participantCode);
        throw $exception;
    }
    /** **********************************************************************
     * find participant field
     *
     * @param   string $participantCode             participant code
     * @return  ParticipantFieldsSet                participant fields set
     * @throws  UnknownParticipantException         participant fields set not found
     ************************************************************************/
    private function findParticipantFieldsSet(string $participantCode) : ParticipantFieldsSet
    {
        try
        {
            $participant = $this->findParticipant($participantCode);
            return $this->participantsFieldsCollection->get($participant);
        }
        catch (UnknownParticipantException $exception)
        {
            $needException = new UnknownParticipantException;
            $needException->setParticipantCode($participantCode);
            throw $needException;
        }
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
        $participantFieldsSet   = null;
        $needException          = new UnknownParticipantFieldException;

        $needException->setParticipantCode($participantCode);
        $needException->setParticipantFieldName($fieldName);

        try
        {
            $participantFieldsSet = $this->findParticipantFieldsSet($participantCode);
        }
        catch (UnknownParticipantException $exception)
        {
            throw $needException;
        }

        $participantFieldsSet->rewind();
        while ($participantFieldsSet->valid())
        {
            $participantField = $participantFieldsSet->current();
            if ($participantField->getParam('name') == $fieldName)
            {
                return $participantField;
            }
            $participantFieldsSet->next();
        }

        throw $needException;
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
        $this->procedureFieldsCollection->rewind();
        while ($this->procedureFieldsCollection->valid())
        {
            $procedureField = $this->procedureFieldsCollection->current();
            if ($procedureField->getParam('id') == $procedureFieldId)
            {
                return $procedureField;
            }
            $this->procedureFieldsCollection->next();
        }

        throw new UnknownProcedureFieldException;
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