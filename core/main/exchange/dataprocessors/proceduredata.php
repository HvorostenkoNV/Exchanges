<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    UnexpectedValueException,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\Procedures\Fields\ProcedureField;
/** ***********************************************************************************************
 * Procedure data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class ProcedureData
{
    private
        $procedure              = null,
        $data                   = [],
        $procedureFieldsIdMap   = null;
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure $procedure            procedure
     ************************************************************************/
    public function __construct(Procedure $procedure)
    {
        $this->procedure            = $procedure;
        $this->data                 = $this->getProcedureData($procedure);
        $this->procedureFieldsIdMap = $this->getProcedureFieldIdMap($procedure);
    }
    /** **********************************************************************
     * get items id array
     *
     * @return  int[]                           items id array
     ************************************************************************/
    public function getItemsIdArray() : array
    {
        return array_keys($this->data);
    }
    /** **********************************************************************
     * get data
     *
     * @param   int             $commonItemId   common item id
     * @param   ProcedureField  $procedureField procedure field
     * @return  mixed                           value
     * @throws  UnexpectedValueException        no data was found
     ************************************************************************/
    public function getData(int $commonItemId, ProcedureField $procedureField)
    {
        $procedureFieldId = $this->procedureFieldsIdMap->get($procedureField);

        if
        (
            array_key_exists($commonItemId, $this->data) &&
            array_key_exists($procedureFieldId, $this->data[$commonItemId])
        )
        {
            return $this->data[$commonItemId][$procedureFieldId];
        }

        throw new UnexpectedValueException;
    }
    /** **********************************************************************
     * set data
     *
     * @param   int             $commonItemId   common item id
     * @param   ProcedureField  $procedureField procedure field
     * @param   mixed           $data           value
     * @throws  RuntimeException                setting data error
     ************************************************************************/
    public function setData(int $commonItemId, ProcedureField $procedureField, $data) : void
    {
        try
        {
            $db                 = DB::getInstance();
            $value              = serialize($data);
            $procedureFieldId   = $this->procedureFieldsIdMap->get($procedureField);
            $dbRecordId         = 0;

            $sqlQuery       = "SELECT `ID` FROM matched_items_data WHERE `PROCEDURE_ITEM` = ? AND `PROCEDURE_FIELD` = ?";
            $queryResult    = $db->query($sqlQuery, [$commonItemId, $procedureFieldId]);
            while ($queryResult->count() > 0)
            {
                $dbRecordId = (int) $queryResult->pop()->get('ID');
            }

            if ($dbRecordId > 0)
            {
                $db->query
                (
                    "UPDATE matched_items_data SET `DATA` = ? WHERE `ID` = ?",
                    [$value, $dbRecordId]
                );
            }
            else
            {
                $db->query
                (
                    "INSERT INTO matched_items_data (`PROCEDURE_ITEM`, `PROCEDURE_FIELD`, `DATA`) VALUES (?, ?, ?)",
                    [$commonItemId, $procedureFieldId, $value]
                );
            }

            if ($db->hasLastError())
            {
                throw new RuntimeException($db->getLastError());
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get procedure data
     *
     * @param   Procedure $procedure            procedure
     * @return  array                           procedure data
     ************************************************************************/
    private function getProcedureData(Procedure $procedure) : array
    {
        try
        {
            $result         = [];
            $db             = DB::getInstance();
            $queryString    = "
                SELECT
                    matched_items_data.`PROCEDURE_ITEM`,
                    matched_items_data.`PROCEDURE_FIELD`,
                    matched_items_data.`DATA`
                FROM
                    matched_items_data
                INNER JOIN matched_items
                    ON matched_items_data.`PROCEDURE_ITEM` = matched_items.`ID`
                INNER JOIN procedures
                    ON matched_items.`PROCEDURE` = procedures.`ID`
                WHERE
                    procedures.`CODE` = ?";

            $queryResult = $db->query($queryString, [$procedure->getCode()]);
            while ($queryResult->count() > 0)
            {
                $item               = $queryResult->pop();
                $commonItemId       = (int) $item->get('PROCEDURE_ITEM');
                $procedureFieldId   = (int) $item->get('PROCEDURE_FIELD');
                $data               = unserialize($item->get('DATA'));

                if (!array_key_exists($commonItemId, $result))
                {
                    $result[$commonItemId] = [];
                }
                $result[$commonItemId][$procedureFieldId] = $data;
            }

            return $result;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Procedure data container: error on query items data, \"$error\"");
            return [];
        }
    }
    /** **********************************************************************
     * get procedure fields id map
     *
     * @param   Procedure $procedure            procedure
     * @return  MapData                         procedure fields id map
     ************************************************************************/
    private function getProcedureFieldIdMap(Procedure $procedure) : MapData
    {
        $result             = new MapData;
        $procedureFieldsSet = $procedure->getFields();

        if ($procedureFieldsSet->count() <= 0)
        {
            Logger::getInstance()->addWarning("Procedure data container: procedure has no fields");
            return $result;
        }

        $procedureFieldsObjectsArray    = [];
        $procedureFieldsIdArray         = [];
        $procedureFieldsQueryResult     = $this->queryProcedureFields($procedure);

        while ($procedureFieldsSet->valid())
        {
            $procedureField             = $procedureFieldsSet->current();
            $procedureFieldNameParts    = [];

            $procedureField->rewind();
            while ($procedureField->valid())
            {
                $participantField       = $procedureField->current();
                $participantFieldName   = $participantField->getField()->getParam('name');
                $participantCode        = $participantField->getParticipant()->getCode();
                $procedureFieldNameParts[] = "$participantCode-$participantFieldName";
                $procedureField->next();
            }

            asort($procedureFieldNameParts);
            $procedureFieldsObjectsArray[implode('|', $procedureFieldNameParts)] = $procedureField;
            $procedureFieldsSet->next();
        }

        foreach ($procedureFieldsQueryResult as $procedureFieldId => $procedureFieldStructure)
        {
            $procedureFieldNameParts = [];
            foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
            {
                $procedureFieldNameParts[] = "$participantCode-$participantFieldName";
            }
            asort($procedureFieldNameParts);
            $procedureFieldsIdArray[implode('|', $procedureFieldNameParts)] = $procedureFieldId;
        }

        foreach ($procedureFieldsObjectsArray as $procedureFieldUniqueName => $procedureField)
        {
            if (array_key_exists($procedureFieldUniqueName, $procedureFieldsIdArray))
            {
                $procedureFieldId = $procedureFieldsIdArray[$procedureFieldUniqueName];
                $result->set($procedureField, $procedureFieldId);
            }
        }
        return $result;
    }
    /** **********************************************************************
     * query procedure fields
     *
     * @param   Procedure $procedure            procedure
     * @return  array                           procedure fields info
     ************************************************************************/
    private function queryProcedureFields(Procedure $procedure) : array
    {
        try
        {
            $result         = [];
            $db             = DB::getInstance();
            $queryString    = "
                SELECT
                    procedures_participants_fields.`PROCEDURE_FIELD`  AS PROCEDURE_FIELD_ID,
                    participants.`CODE`                               AS PARTICIPANT_CODE,
                    participants_fields.`NAME`                        AS PARTICIPANT_FIELD_NAME
                FROM
                    procedures_participants_fields
                INNER JOIN procedures_fields
                    ON procedures_participants_fields.`PROCEDURE_FIELD` = procedures_fields.`ID`
                INNER JOIN procedures
                    ON procedures_fields.`PROCEDURE` = procedures.`ID`
                INNER JOIN participants_fields
                    ON procedures_participants_fields.`PARTICIPANT_FIELD` = participants_fields.`ID`
                INNER JOIN participants
                    ON participants_fields.`PARTICIPANT` = participants.`ID`
                WHERE
                    procedures.`CODE` = ?";

            $queryResult = $db->query($queryString, [$procedure->getCode()]);
            while ($queryResult->count() > 0)
            {
                $item                   = $queryResult->pop();
                $procedureFieldId       = (int) $item->get('PROCEDURE_FIELD_ID');
                $participantFieldName   = $item->get('PARTICIPANT_FIELD_NAME');
                $participantCode        = $item->get('PARTICIPANT_CODE');

                if (!array_key_exists($procedureFieldId, $result))
                {
                    $result[$procedureFieldId] = [];
                }
                $result[$procedureFieldId][$participantCode] = $participantFieldName;
            }

            return $result;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Procedure data container: error on query procedure fields, \"$error\"");
            return [];
        }
    }
}