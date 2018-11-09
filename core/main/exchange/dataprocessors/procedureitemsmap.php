<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    RuntimeException,
    UnexpectedValueException,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    Main\Helpers\Logger,
    Main\Helpers\Database\DB,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Procedure items map container
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class ProcedureItemsMap
{
    private
        $procedure          = null,
        $itemsMap           = [],
        $participantsIdMap  = [],
        $procedureId        = 0;
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure $procedure            procedure
     ************************************************************************/
    public function __construct(Procedure $procedure)
    {
        $this->procedure            = $procedure;
        $this->itemsMap             = $this->getItemsMap($procedure);
        $this->participantsIdMap    = $this->getParticipantsIdMap($procedure);
        $this->procedureId          = $this->getProcedureId($procedure);
    }
    /** **********************************************************************
     * get items id array
     *
     * @return  int[]                           items id array
     ************************************************************************/
    public function getItemsIdArray() : array
    {
        return array_keys($this->itemsMap);
    }
    /** **********************************************************************
     * get participant item id by common item id
     *
     * @param   Participant $participant        participant
     * @param   int         $commonItemId       common item id
     * @return  string                          participant item id
     * @throws  UnexpectedValueException        participant item id not found
     ************************************************************************/
    public function getItemId(Participant $participant, int $commonItemId) : string
    {
        $participantCode = $participant->getCode();

        if
        (
            array_key_exists($commonItemId, $this->itemsMap) &&
            array_key_exists($participantCode, $this->itemsMap[$commonItemId])
        )
        {
            return $this->itemsMap[$commonItemId][$participantCode];
        }

        throw new UnexpectedValueException;
    }
    /** **********************************************************************
     * get common item id by participant item id
     *
     * @param   Participant $participant        participant
     * @param   string      $participantItemId  participant item id
     * @return  int                             common item id
     * @throws  UnexpectedValueException        common item id not found
     ************************************************************************/
    public function getItemCommonId(Participant $participant, string $participantItemId) : int
    {
        $participantCode = $participant->getCode();

        foreach ($this->itemsMap as $commonItemId => $participantsItems)
        {
            if
            (
                array_key_exists($participantCode, $participantsItems) &&
                $participantsItems[$participantCode] == $participantItemId
            )
            {
                return $commonItemId;
            }
        }

        throw new UnexpectedValueException;
    }
    /** **********************************************************************
     * set participant item
     *
     * @param   int         $commonItemId       common item id
     * @param   Participant $participant        participant
     * @param   string      $participantItemId  participant item id
     * @return  void
     * @throws  RuntimeException                adding participant item error
     ************************************************************************/
    public function setParticipantItem(int $commonItemId, Participant $participant, string $participantItemId) : void
    {
        $participantCode    = $participant->getCode();
        $participantId      = array_key_exists($participantCode, $this->participantsIdMap)
            ? $this->participantsIdMap[$participantCode]
            : 0;

        if (!array_key_exists($commonItemId, $this->itemsMap))
        {
            throw new RuntimeException("procedure item with \"$commonItemId\" ID is not exist");
        }
        if ($participantId <= 0)
        {
            throw new RuntimeException("participant \"$participantCode\" was not found");
        }

        try
        {
            $oldCommonItemId = $this->getItemCommonId($participant, $participantItemId);
            if ($oldCommonItemId === $commonItemId)
            {
                return;
            }
            else
            {
                $this->unbindParticipantItem($oldCommonItemId, $participant, $participantItemId);
            }
        }
        catch (UnexpectedValueException $exception)
        {

        }

        try
        {
            DB::getInstance()->query
            (
                "INSERT INTO matched_items_participants (`PROCEDURE_ITEM`, `PARTICIPANT`, `PARTICIPANT_ITEM_ID`) VALUES (?, ?, ?)",
                [$commonItemId, $participantId, $participantItemId]
            );

            $this->itemsMap[$commonItemId][$participantCode] = $participantItemId;
        }
        catch (DBConnectionException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * create new common item
     *
     * @param   Participant $participant        participant
     * @param   string      $participantItemId  participant item id
     * @return  int                             new common item id
     * @throws  RuntimeException                adding new common item error
     ************************************************************************/
    public function createNewItem(Participant $participant, string $participantItemId) : int
    {
        $participantCode    = $participant->getCode();
        $participantId      = array_key_exists($participantCode, $this->participantsIdMap)
            ? $this->participantsIdMap[$participantCode]
            : 0;

        if ($participantId <= 0)
        {
            throw new RuntimeException("participant \"$participantCode\" was not found");
        }

        try
        {
            $db = DB::getInstance();

            $db->query
            (
                "INSERT INTO matched_items (`PROCEDURE`) VALUES (?)",
                [$this->procedureId]
            );
            $newCommonItemId = $db->getLastInsertId();
            $db->query
            (
                "INSERT INTO matched_items_participants (`PROCEDURE_ITEM`, `PARTICIPANT`, `PARTICIPANT_ITEM_ID`) VALUES (?, ?, ?)",
                [$newCommonItemId, $participantId, $participantItemId]
            );

            $this->itemsMap[$newCommonItemId] = [$participantCode => $participantItemId];
            return $newCommonItemId;
        }
        catch (DBConnectionException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * get items map
     *
     * @param   Procedure $procedure            procedure
     * @return  array                           items map
     ************************************************************************/
    private function getItemsMap(Procedure $procedure) : array
    {
        $result         = [];
        $queryString    = "
            SELECT
                matched_items_participants.`PROCEDURE_ITEM`       AS COMMON_ITEM_ID,
                participants.`CODE`                               AS PARTICIPANT_CODE,
                matched_items_participants.`PARTICIPANT_ITEM_ID`  AS PARTICIPANT_ITEM_ID
            FROM
                matched_items_participants
            INNER JOIN matched_items
                ON matched_items_participants.`PROCEDURE_ITEM` = matched_items.`ID`
            INNER JOIN procedures
                ON matched_items.`PROCEDURE` = procedures.`ID`
            INNER JOIN participants
                ON matched_items_participants.`PARTICIPANT` = participants.`ID`
            WHERE
                procedures.`CODE` = ?";
        $queryResult    = null;

        try
        {
            $queryResult = DB::getInstance()->query($queryString, [$procedure->getCode()]);
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
                $item               = $queryResult->pop();
                $commonItemId       = (int) $item->get('COMMON_ITEM_ID');
                $participantCode    = $item->get('PARTICIPANT_CODE');
                $participantItemId  = $item->get('PARTICIPANT_ITEM_ID');

                if (!array_key_exists($commonItemId, $result))
                {
                    $result[$commonItemId] = [];
                }
                $result[$commonItemId][$participantCode] = $participantItemId;
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participants id map
     *
     * @param   Procedure $procedure            procedure
     * @return  array                           participants id map
     ************************************************************************/
    private function getParticipantsIdMap(Procedure $procedure) : array
    {
        try
        {
            $result                 = [];
            $participantsSet        = $procedure->getParticipants();
            $participantsCodeArray  = [];

            while ($participantsSet->valid())
            {
                $participantsCodeArray[] = $participantsSet->current()->getCode();
                $participantsSet->next();
            }

            if (count($participantsCodeArray) <= 0)
            {
                Logger::getInstance()->addWarning("Procedure items map container: error on query participants id map, procedure has no participants");
                return [];
            }

            $participantsPlaceholder    = rtrim(str_repeat('?, ', count($participantsCodeArray)), ', ');
            $queryString                = "SELECT participants.`ID`, participants.`CODE` FROM participants WHERE participants.`CODE` IN ($participantsPlaceholder)";
            $queryResult                = DB::getInstance()->query($queryString, $participantsCodeArray);

            while (!$queryResult->isEmpty())
            {
                $item                       = $queryResult->pop();
                $participantId              = (int) $item->get('ID');
                $participantCode            = $item->get('CODE');
                $result[$participantCode]   = $participantId;
            }

            return $result;
        }
        catch (DBConnectionException $exception)
        {
            return [];
        }
        catch (DBQueryException $exception)
        {
            return [];
        }
        catch (RuntimeException $exception)
        {
            return [];
        }
    }
    /** **********************************************************************
     * get procedure id
     *
     * @param   Procedure $procedure            procedure
     * @return  int                             procedure id
     ************************************************************************/
    private function getProcedureId(Procedure $procedure) : int
    {
        try
        {
            $queryString    = "SELECT procedures.`ID` FROM procedures WHERE procedures.`CODE` = ?";
            $queryResult    = DB::getInstance()->query($queryString, [$procedure->getCode()]);

            while (!$queryResult->isEmpty())
            {
                return (int) $queryResult->pop()->get('ID');
            }

            return 0;
        }
        catch (DBConnectionException $exception)
        {
            return 0;
        }
        catch (DBQueryException $exception)
        {
            return 0;
        }
        catch (RuntimeException $exception)
        {
            return 0;
        }
    }
    /** **********************************************************************
     * unbind participant item
     *
     * @param   int         $commonItemId       common item id
     * @param   Participant $participant        participant
     * @param   string      $participantItemId  participant item id
     * @return  void
     ************************************************************************/
    private function unbindParticipantItem(int $commonItemId, Participant $participant, string $participantItemId) : void
    {
        $participantCode    = $participant->getCode();
        $participantId      = array_key_exists($participantCode, $this->participantsIdMap)
            ? $this->participantsIdMap[$participantCode]
            : 0;
        $queryString        = "
            SELECT
                matched_items_participants.`ID`,
                matched_items_participants.`PROCEDURE_ITEM`,
                matched_items_participants.`PARTICIPANT_ITEM_ID`
            FROM
                matched_items_participants
            INNER JOIN matched_items
                ON matched_items_participants.`PROCEDURE_ITEM` = matched_items.`ID`
            WHERE
                matched_items.`ID` = ? AND
                matched_items_participants.`PARTICIPANT_ITEM_ID` = ?";

        if
        (
            !array_key_exists($commonItemId, $this->itemsMap)                   ||
            !array_key_exists($participantCode, $this->itemsMap[$commonItemId]) ||
            $participantId <= 0
        )
        {
            return;
        }

        try
        {
            $db             = DB::getInstance();
            $queryResult    = $db->query($queryString, [$commonItemId, $participantItemId]);

            if ($queryResult->count() == 1)
            {
                $db->query("DELETE FROM matched_items WHERE `ID` = ?", [$commonItemId]);
                unset($this->itemsMap[$commonItemId]);
            }
            else
            {
                while (!$queryResult->isEmpty())
                {
                    $item = $queryResult->pop();
                    if ($item->get('PARTICIPANT_ITEM_ID') == $commonItemId)
                    {
                        $db->query("DELETE FROM matched_items_participants WHERE `ID` = ?", [$item->get('ID')]);
                        unset($this->itemsMap[$commonItemId][$participantCode]);
                        break;
                    }
                }
            }
        }
        catch (DBConnectionException $exception)
        {

        }
        catch (DBQueryException $exception)
        {

        }
        catch (RuntimeException $exception)
        {

        }
    }
}