<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    InvalidArgumentException,
    ReflectionClass,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Helpers\Data\DBQueryResult,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Fields\FieldsSet,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Application participant abstract class
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipants implements Participant
{
    private $fields = null;
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsSet                   fields params collection
     ************************************************************************/
    final public function getFields() : FieldsSet
    {
        $logger             = Logger::getInstance();
        $participantName    = static::class;

        if (!$this->fields)
        {
            $this->fields = $this->constructFields();
            if ($this->fields->count() <= 0)
            {
                $logger->addWarning("Participant \"$participantName\" has no fields");
            }
        }

        return $this->fields;
    }
    /** **********************************************************************
     * get participant provided data
     *
     * @return  ProvidedData                provided data
     ************************************************************************/
    final public function getProvidedData() : ProvidedData
    {
        $data               = $this->readProvidedData();
        $logger             = Logger::getInstance();
        $participantName    = static::class;

        if ($data->count() <= 0)
        {
            $logger->addNotice("Geted provided data from \"$participantName\" is empty");
        }

        return $data;
    }
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   DataForDelivery $data       data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    final public function deliveryData(DataForDelivery $data) : bool
    {
        $logger             = Logger::getInstance();
        $participantName    = static::class;

        if ($data->count() <= 0)
        {
            $logger->addNotice("\"$participantName\" data for delivery is empty");
        }

        return $this->provideDataForDelivery($data);
    }
    /** **********************************************************************
     * construct fields
     *
     * @return  FieldsSet                   fields params collection
     ************************************************************************/
    private function constructFields() : FieldsSet
    {
        $result             = new FieldsSet;
        $logger             = Logger::getInstance();
        $queryResult        = null;
        $participantName    = static::class;

        try
        {
            $queryResult = $this->queryFieldsInfo();
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to get \"$participantName\" participant fields info: $error");
            return $result;
        }

        try
        {
            while (!$queryResult->isEmpty())
            {
                $fieldDb        = $queryResult->pop();
                $fieldType      = $fieldDb->get('TYPE');
                $fieldName      = $fieldDb->get('NAME');
                $fieldRequired  = $fieldDb->get('IS_REQUIRED');

                try
                {
                    $fieldParams = new MapData;
                    $fieldParams->set('type', $fieldType);
                    $fieldParams->set('name', $fieldName);
                    $fieldParams->set('required', $fieldRequired == 'Y');

                    $field = new Field($fieldParams);
                    $result->push($field);
                }
                catch (InvalidArgumentException $exception)
                {
                    $error = $exception->getMessage();
                    $logger->addWarning("Unable to create participant field \"$fieldName\" for \"$participantName\": $error");
                }
            }
        }
        catch (RuntimeException $exception)
        {

        }

        $result->rewind();
        return $result;
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
     * @param   DataForDelivery $data       data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function provideDataForDelivery(DataForDelivery $data) : bool;
}