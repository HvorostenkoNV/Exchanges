<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    UnexpectedValueException,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\Procedures\Fields\Field as ProcedureField;
/** ***********************************************************************************************
 * Procedure data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class ProcedureData
{
    private
        $procedure  = null,
        $data       = [];
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure $procedure            procedure
     ************************************************************************/
    public function __construct(Procedure $procedure)
    {
        $this->procedure    = $procedure;
        $this->data         = $this->getProcedureData($procedure);
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
        if ($procedureField->getProcedure()->getCode() != $this->procedure->getCode())
        {
            throw new UnexpectedValueException('procedure field not belong to current procedure');
        }

        $procedureFieldId = $procedureField->getParam('id');
        if
        (
            array_key_exists($commonItemId, $this->data) &&
            array_key_exists($procedureFieldId, $this->data[$commonItemId])
        )
        {
            return $this->data[$commonItemId][$procedureFieldId];
        }

        throw new UnexpectedValueException("item \"$commonItemId\" was not found");
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
        if ($procedureField->getProcedure()->getCode() != $this->procedure->getCode())
        {
            throw new RuntimeException('procedure field not belong to current procedure');
        }

        try
        {
            $db                 = DB::getInstance();
            $value              = serialize($data);
            $procedureFieldId   = $procedureField->getParam('id');
            $sqlQuery           = "SELECT `ID` FROM matched_items_data WHERE `PROCEDURE_ITEM` = ? AND `PROCEDURE_FIELD` = ?";
            $queryResult        = $db->query($sqlQuery, [$commonItemId, $procedureFieldId]);
            $dbRecordId         = 0;

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
}