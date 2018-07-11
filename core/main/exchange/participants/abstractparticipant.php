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
    Main\Exchange\Participants\Data\Data;
/** ***********************************************************************************************
 * Application participant abstract class
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipant implements Participant
{
    private static
        $idFieldType        = 'item-id';
    private
        $code               = '',
        $fieldsCollection   = null,
        $idField            = null;
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

        $this->fieldsCollection = $this->constructFieldsCollection();
        if ($this->fieldsCollection->count() <= 0)
        {
            $this->addLogMessage('fields collection is empty', 'warning');
        }

        $this->fieldsCollection->rewind();
        while ($this->fieldsCollection->valid())
        {
            $field = $this->fieldsCollection->current();
            if ($field->getParam('type') == self::$idFieldType)
            {
                $this->idField = $field;
                break;
            }
            $this->fieldsCollection->next();
        }
        if (!$this->idField)
        {
            $this->addLogMessage('has no ID field', 'warning');
        }
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
        $this->fieldsCollection->rewind();
        return $this->fieldsCollection;
    }
    /** **********************************************************************
     * get participant provided data
     *
     * @return  Data                        provided data
     ************************************************************************/
    final public function getProvidedData() : Data
    {
        $fields = $this->getFields();
        $data   = $this->readProvidedData($fields);
        $data   = $this->validateData($data);

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
     * @param   Data $data                  data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    final public function deliveryData(Data $data) : bool
    {
        if ($data->count() <= 0)
        {
            $this->addLogMessage('data for delivery is empty', 'notice');
        }

        $this->addLogMessage('data delivering process run', 'notice');
        return $this->provideDataForDelivery($data);
    }
    /** **********************************************************************
     * construct participant fields collection
     *
     * @return  FieldsSet                   fields collection
     ************************************************************************/
    private function constructFieldsCollection() : FieldsSet
    {
        $result         = new FieldsSet;
        $queryResult    = null;

        try
        {
            $queryResult = $this->queryFieldsInfo();
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $this->addLogMessage("fields query failed, \"$error\"", 'warning');
            return $result;
        }

        foreach ($queryResult as $item)
        {
            $fieldParams = new MapData;
            $fieldParams->set('id',         is_numeric($item['ID']) ? (int) $item['ID'] : $item['ID']);
            $fieldParams->set('name',       (string) $item['NAME']);
            $fieldParams->set('type',       (string) $item['TYPE']);
            $fieldParams->set('required',   $item['IS_REQUIRED'] == 'Y');

            try
            {
                $field = new Field($this, $fieldParams);
                $result->push($field);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("unexpected error on constructing fields collection, \"$error\"", 'warning');
            }
        }

        return $result;
    }
    /** **********************************************************************
     * validate data
     *
     * @param   Data $data                  data
     * @return  Data                        validated data
     ************************************************************************/
    private function validateData(Data $data) : Data
    {
        $dataSize = $data->count();

        for ($index = $dataSize; $index > 0; $index--)
        {
            try
            {
                $item       = $data->pop();
                $itemHasId  = $this->idField && $item->hasKey($this->idField);

                if ($itemHasId)
                {
                    $data->push($item);
                }
            }
            catch (RuntimeException $exception)
            {

            }
            catch (InvalidArgumentException $exception)
            {

            }
        }

        return $data;
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
                    participants_fields.`ID`,
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
     * @return  Data                        data
     ************************************************************************/
    abstract protected function readProvidedData(FieldsSet $fields) : Data;
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   Data $data                  data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function provideDataForDelivery(Data $data) : bool;
}