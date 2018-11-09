<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    UnexpectedValueException,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    Main\Helpers\Database\DB,
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
        $this->procedure = $procedure;

        try
        {
            $this->data = $this->getProcedureData($procedure);
        }
        catch (DBConnectionException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Procedure data container: error on query items data, \"$error\"");
        }
        catch (DBQueryException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Procedure data container: error on query items data, \"$error\"");
        }
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
     * @return  void
     * @throws  RuntimeException                setting data error
     ************************************************************************/
    public function setData(int $commonItemId, ProcedureField $procedureField, $data) : void
    {
        try
        {
            $value              = serialize($data);
            $procedureFieldId   = $procedureField->getParam('id');
            $sqlQuery           = "SELECT `ID` FROM matched_items_data WHERE `PROCEDURE_ITEM` = ? AND `PROCEDURE_FIELD` = ?";
            $dbRecordId         = 0;
            $db                 = DB::getInstance();
            $queryResult        = $db->query($sqlQuery, [$commonItemId, $procedureFieldId]);

            if ($procedureField->getProcedure()->getCode() != $this->procedure->getCode())
            {
                throw new RuntimeException('procedure field not belong to current procedure');
            }

            while (!$queryResult->isEmpty())
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
        }
        catch (DBConnectionException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw new RuntimeException($exception->getMessage());
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
     * @throws  DBConnectionException           db connection error
     * @throws  DBQueryException                db query error
     ************************************************************************/
    private function getProcedureData(Procedure $procedure) : array
    {
        $result         = [];
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
        $queryResult    = null;

        try
        {
            $queryResult = DB::getInstance()->query($queryString, [$procedure->getCode()]);
        }
        catch (DBConnectionException $exception)
        {
            throw $exception;
        }
        catch (DBQueryException $exception)
        {
            throw $exception;
        }

        while (!$queryResult->isEmpty())
        {
            try
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
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
}