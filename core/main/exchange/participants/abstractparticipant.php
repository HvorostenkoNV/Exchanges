<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    InvalidArgumentException,
    ReflectionException,
    ReflectionClass,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
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
    private
        $code               = '',
        $fieldsCollection   = [];
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

        }

        $this->fieldsCollection = $this->getFieldsCollection();

        $this->addLogMessage('created', 'notice');
    }
    /** **********************************************************************
     * get participant code
     *
     * @return  string                      participant code
     ************************************************************************/
    final public function getCode() : string
    {
        return $this->code;
    }
    /** **********************************************************************
     * get participant fields set
     *
     * @return  FieldsSet                   fields params collection
     ************************************************************************/
    final public function getFields() : FieldsSet
    {
        $result = new FieldsSet;

        if (count($this->fieldsCollection) <= 0)
        {
            $this->addLogMessage('has no fields', 'warning');
            return $result;
        }

        foreach ($this->fieldsCollection as $field)
        {
            try
            {
                $result->push($field);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unknown error on constructing fields set, \"$error\"", 'warning');
            }
        }

        $this->addLogMessage('fields set constructed and returned', 'notice');
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
        $fields = $this->getFields();
        $data   = $this->readProvidedData($fields);

        if ($data->count() <= 0)
        {
            $this->addLogMessage('geted provided data is empty', 'notice');
        }

        $this->addLogMessage('provided data gathered and returned', 'notice');
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
        if ($data->count() <= 0)
        {
            $this->addLogMessage('data for delivery is empty', 'notice');
        }

        $this->addLogMessage('data delivering process run', 'notice');
        return $this->provideDataForDelivery($data);
    }
    /** **********************************************************************
     * get fields collection
     *
     * @return  array                       fields collection
     * @example
     * [
     *      fieldName   => field,
     *      fieldName   => field
     * ]
     ************************************************************************/
    private function getFieldsCollection() : array
    {
        $result = [];

        try
        {
            $queryResult = $this->queryFieldsInfo();
            foreach ($queryResult as $item)
            {
                $fieldParams = new MapData;
                $fieldParams->set('type', $item['TYPE']);
                $fieldParams->set('name', $item['NAME']);
                $fieldParams->set('required', $item['IS_REQUIRED'] == 'Y');

                $field = new Field($fieldParams);
                $result[$item['NAME']] = $field;
            }
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("fields query failed, \"$error\"", 'warning');
        }
        catch (InvalidArgumentException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("unable to create participant field, \"$error\"", 'warning');
        }

        return $result;
    }
    /** **********************************************************************
     * query participant fields info from database
     *
     * @return  array                       query result
     * @throws  RuntimeException            query error
     ************************************************************************/
    private function queryFieldsInfo() : array
    {
        try
        {
            $result     = [];
            $db         = DB::getInstance();
            $sqlQuery   = '
                SELECT
                    participants_fields.`NAME`,
                    participants_fields.`IS_REQUIRED`,
                    fields_types.`CODE` AS TYPE
                FROM
                    participants_fields
                INNER JOIN participants
                    ON participants_fields.`PARTICIPANT` = participants.`ID`
                INNER JOIN fields_types
                    ON participants_fields.`TYPE` = fields_types.`ID`
                WHERE
                    participants.`CODE` = ?';

            $queryResult = $db->query($sqlQuery, [$this->getCode()]);
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
     * add message to log
     *
     * @param   string  $message            message
     * @param   string  $type               message type
     ************************************************************************/
    private function addLogMessage(string $message, string $type) : void
    {
        $logger         = Logger::getInstance();
        $code           = $this->getCode();
        $fullMessage    = "Participant \"$code\": $message";

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
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @param   FieldsSet $fields           participant fields set
     * @return  ProvidedData                data
     ************************************************************************/
    abstract protected function readProvidedData(FieldsSet $fields) : ProvidedData;
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   DataForDelivery $data       data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function provideDataForDelivery(DataForDelivery $data) : bool;
}