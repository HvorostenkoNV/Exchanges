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
abstract class AbstractParticipant implements Participant
{
    private $fields = [];
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        $this->fields = $this->constructFieldsCollection();
    }
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsSet                   fields params collection
     ************************************************************************/
    final public function getFields() : FieldsSet
    {
        $result         = new FieldsSet;
        $logger         = Logger::getInstance();
        $participant    = static::class;

        if (count($this->fields) <= 0)
        {
            $logger->addWarning("Participant \"$participant\" has no fields");
            return $result;
        }

        try
        {
            foreach ($this->fields as $field)
            {
                $result->push($field);
            }
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Error on filling participant \"$participant\" fields set: $error");
        }

        $logger->addNotice("Participant \"$participant\" fields set constructed and returned");
        $result->rewind();
        return $result;
    }
    /** **********************************************************************
     * get participant provided data
     *
     * @return  ProvidedData                provided data
     ************************************************************************/
    final public function getProvidedData() : ProvidedData
    {
        $data           = $this->readProvidedData();
        $logger         = Logger::getInstance();
        $participant    = static::class;

        if ($data->count() <= 0)
        {
            $logger->addNotice("Geted provided data from \"$participant\" participant is empty");
        }

        $logger->addNotice("Participant \"$participant\" provided data gathered and returned");
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
        $logger         = Logger::getInstance();
        $participant    = static::class;

        if ($data->count() <= 0)
        {
            $logger->addNotice("\"$participant\" participant data for delivery is empty");
        }

        $logger->addNotice("Participant \"$participant\" delivering data process run");
        return $this->provideDataForDelivery($data);
    }
    /** **********************************************************************
     * create fields collection
     *
     * @return  array                       fields collection
     * @example
     * [
     *      fieldName   => field,
     *      fieldName   => field
     * ]
     ************************************************************************/
    private function constructFieldsCollection() : array
    {
        $logger         = Logger::getInstance();
        $participant    = static::class;
        $result         = [];

        try
        {
            $queryResult = $this->queryFieldsInfo();
            while (!$queryResult->isEmpty())
            {
                $fieldDb        = $queryResult->pop();
                $fieldType      = $fieldDb->get('TYPE');
                $fieldName      = $fieldDb->get('NAME');
                $fieldRequired  = $fieldDb->get('IS_REQUIRED');

                $fieldParams = new MapData;
                $fieldParams->set('type', $fieldType);
                $fieldParams->set('name', $fieldName);
                $fieldParams->set('required', $fieldRequired == 'Y');

                $field = new Field($fieldParams);
                $result[$fieldName] = $field;
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Failed to query fields for participant \"$participant\": $error");
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("Unable to create participant field for \"$participant\": $error");
        }

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
        $reflection         = new ReflectionClass(static::class);
        $participantCode    = $reflection->getShortName();
        $sqlQuery           = '
            SELECT
                participants_fields.`NAME`,
                participants_fields.`IS_REQUIRED`,
                fields_types.`CODE` AS TYPE
            FROM
                participants_fields
            INNER JOIN participants
                ON participants_fields.`PARTICIPANT` = participants.ID
            INNER JOIN fields_types
                ON participants_fields.`TYPE` = fields_types.ID
            WHERE
                participants.`CODE` = ?';

        try
        {
            return DB::getInstance()->query($sqlQuery, [$participantCode]);
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