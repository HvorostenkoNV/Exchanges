<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Throwable,
    RuntimeException,
    ReflectionClass,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Helpers\Data\DBQueryResult,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Procedures\Data\ParticipantsQueue;
/** ***********************************************************************************************
 * Application procedure abstract class
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractProcedure implements Procedure
{
    /** **********************************************************************
     * get participants
     *
     * @return  ParticipantsQueue           participants collection
     * @throws
     ************************************************************************/
    final public function getParticipants() : ParticipantsQueue
    {
        $result         = new ParticipantsQueue;
        $logger         = Logger::getInstance();
        $queryResult    = null;

        try
        {
            $queryResult = $this->queryParticipants();
        }
        catch (RuntimeException $exception)
        {
            $error          = $exception->getMessage();
            $procedureName  = static::class;

            $logger->addWarning("Failed to get \"$procedureName\" procedure participants: $error");
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            $participantName        = $queryResult->pop()->get('NAME');
            $participantClassName   = $this->getParticipantFullClassName($participantName);

            try
            {
                $result->push(new $participantClassName);
            }
            catch (Throwable $exception)
            {
                $error          = $exception->getMessage();
                $procedureName  = static::class;

                $logger->addWarning("Failed to create participant \"$participantClassName\" for procedure \"$procedureName\": $error");
            }
        }

        return $result;
    }
    /** **********************************************************************
     * query procedure participant
     *
     * @return  DBQueryResult               query result
     * @throws  RuntimeException            db connection error
     ************************************************************************/
    private function queryParticipants() : DBQueryResult
    {
        $classReflection    = new ReflectionClass(static::class);
        $classShortName     = $classReflection->getShortName();
        $sqlQuery           = '
            SELECT
                participants.NAME
            FROM
                procedures_participants
            INNER JOIN participants
                ON procedures_participants.PARTICIPANT = participants.ID
            INNER JOIN procedures
                ON procedures_participants.PROCEDURE = procedures.ID
            WHERE
                procedures.NAME = ?';

        try
        {
            return DB::getInstance()->query($sqlQuery, [$classShortName]);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get participant class full name by participant name
     *
     * @param   string  $name               participant name
     * @return  string                      participant class name
     ************************************************************************/
    private function getParticipantFullClassName(string $name) : string
    {
        $classReflection    = new ReflectionClass(Participant::class);
        $classNamespace     = $classReflection->getNamespaceName();

        return $classNamespace.'\\'.$name;
    }
}