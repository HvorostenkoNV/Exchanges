<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    InvalidArgumentException,
    RuntimeException,
    ReflectionClass,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Helpers\Data\DBQueryResult,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\Field,
    Main\Exchange\Participants\Data\FieldsMap,
    Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Application participant abstract class
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipants implements Participant
{
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsMap                   fields params collection
     * @throws
     ************************************************************************/
    final public function getFields() : FieldsMap
    {
        $result         = new FieldsMap;
        $logger         = Logger::getInstance();
        $queryResult    = null;

        try
        {
            $queryResult = $this->queryFieldsInfo();
        }
        catch (RuntimeException $exception)
        {
            $error              = $exception->getMessage();
            $participantName    = static::class;

            $logger->addWarning("failed to get \"$participantName\" participant fields info: $error");
            return $result;
        }

        while (!$queryResult->isEmpty())
        {
            $fieldDb    = $queryResult->pop();
            $field      = new Field;
            $fieldName  = $fieldDb->get('NAME');

            $field->setName($fieldName);
            $field->setType($fieldDb->get('TYPE'));
            $field->setRequired($fieldDb->get('IS_REQUIRED') == 'Y');

            try
            {
                $result->set($field->getName(), $field);
            }
            catch (InvalidArgumentException $exception)
            {
                $error              = $exception->getMessage();
                $participantName    = static::class;

                $logger->addWarning("unable to create \"$participantName\" participant fields \"$fieldName\": $error");
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participant provided data
     *
     * @return  ProvidedData                provided data
     * TODO
     ************************************************************************/
    final public function getProvidedData() : ProvidedData
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * provide data to the participant
     *
     * @param   DeliveredData   $data       provided data
     * @return  bool                        providing data result
     * TODO
     ************************************************************************/
    final public function provideData(DeliveredData $data) : bool
    {
        return false;
    }
    /** **********************************************************************
     * read xml file and get provided data
     *
     * @param   string  $path               xml file path
     * @return  ProvidedData                data
     * TODO
     ************************************************************************/
    protected function readXml(string $path) : ProvidedData
    {
        return new ProvidedData;
    }
    /** **********************************************************************
     * query participant fields info from database
     *
     * @return  DBQueryResult               query result
     * @throws  RuntimeException            db connection error
     ************************************************************************/
    private function queryFieldsInfo() : DBQueryResult
    {
        $classReflection    = new ReflectionClass(static::class);
        $classShortName     = $classReflection->getShortName();
        $sqlQuery           = '
            SELECT
                participants_fields.NAME,
                participants_fields.IS_REQUIRED,
                participants_fields_types.CODE AS TYPE
            FROM
                participants_fields
            INNER JOIN participants
                ON participants_fields.PARTICIPANT = participants.ID
            INNER JOIN participants_fields_types
                ON participants_fields.TYPE = participants_fields_types.ID
            WHERE
                participants.NAME = ?';

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
     * read participant provided data and get it
     *
     * @return  ProvidedData                data
     ************************************************************************/
    abstract protected function readProvidedData() : ProvidedData;
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   DeliveredData   $data       data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function writeDeliveredData(DeliveredData $data) : bool;
}