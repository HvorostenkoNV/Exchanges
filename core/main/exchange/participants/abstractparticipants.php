<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Exception,
    DomainException,
    RuntimeException,
    ReflectionClass,
    InvalidArgumentException,
    SplFileInfo,
    SimpleXMLElement,
    DOMDocument,
    Main\Data\MapData,
    Main\Helpers\DB,
    Main\Helpers\Logger,
    Main\Helpers\Data\DBQueryResult,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Fields\FieldsMap,
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Data\FieldValue,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Application participant abstract class
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipants implements Participant
{
    private $fields = null;
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsMap                   fields params collection
     ************************************************************************/
    final public function getFields() : FieldsMap
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
     * @return  Data                        provided data
     ************************************************************************/
    final public function getProvidedData() : Data
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
     * @param   Data    $data               data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    final public function deliveryData(Data $data) : bool
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
     * read xml file and get data
     *
     * @param   SplFileInfo $xmlFile        xml file
     * @return  Data                        data
     ************************************************************************/
    final protected function readXml(SplFileInfo $xmlFile) : Data
    {
        $result             = new ProvidedData;
        $logger             = Logger::getInstance();
        $xmlData            = $this->readDataFromXml($xmlFile);
        $fields             = $this->getFields();
        $xmlFilePath        = $xmlFile->getPathname();
        $participantName    = static::class;

        foreach ($xmlData as $itemData)
        {
            $item = new ItemData;

            foreach ($itemData as $key => $value)
            {
                $field = $fields->get($key);

                if (!$field)
                {
                    $logger->addNotice("XML file \"$xmlFilePath\" has unknown item key \"$key\" for participant \"$participantName\"");
                    continue;
                }

                try
                {
                    $fieldValue = new FieldValue($value, $field);
                    $item->set($key, $fieldValue);
                }
                catch (DomainException $exception)
                {
                    $error = $exception->getMessage();
                    $logger->addNotice("XML file \"$xmlFilePath\" has invalid value by key \"$key\" in participant \"$participantName\": $error");
                }
                catch (InvalidArgumentException $exception)
                {

                }
            }

            if ($item->count() > 0)
            {
                try
                {
                    $result->push($item);
                }
                catch (InvalidArgumentException $exception)
                {

                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * write data to xml file
     *
     * @param   Data        $data           data
     * @param   SplFileInfo $xmlFile        xml file
     * @return  bool                        writing result
     ************************************************************************/
    final protected function writeXml(Data $data, SplFileInfo $xmlFile) : bool
    {
        $dataArray = [];

        try
        {
            while (!$data->isEmpty())
            {
                $item       = $data->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $key)
                {
                    $itemArray[$key] = $item->get($key)->getPrintableValue();
                }

                if (count($itemArray) > 0)
                {
                    $dataArray[] = $itemArray;
                }
            }
        }
        catch (RuntimeException $exception)
        {

        }

        return $this->writeDataToXml($dataArray, $xmlFile);
    }
    /** **********************************************************************
     * construct fields
     *
     * @return  FieldsMap                   fields params collection
     ************************************************************************/
    private function constructFields() : FieldsMap
    {
        $result             = new FieldsMap;
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
                    $field = new Field(new MapData
                    ([
                        'type'      => $fieldType,
                        'name'      => $fieldName,
                        'required'  => $fieldRequired == 'Y'
                    ]));
                    $result->set($fieldName, $field);
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
     * get data form xml file
     *
     * @param   SplFileInfo $xmlFile        xml file
     * @return  array                       xml data
     ************************************************************************/
    private function readDataFromXml(SplFileInfo $xmlFile) : array
    {
        $result = [];

        try
        {
            $xml            = new SimpleXMLElement($xmlFile->getPathname(), 0, true);
            $recordsNodes   = call_user_func([$xml, 'children']);

            foreach ($recordsNodes as $recordNode)
            {
                $record         = [];
                $fieldsNodes    = call_user_func([$recordNode, 'children']);

                foreach ($fieldsNodes as $fieldNode)
                {
                    $fieldName      = call_user_func([$fieldNode, 'getName']);
                    $valuesNodes    = call_user_func([$fieldNode, 'children']);
                    $fieldValue     = null;

                    if (count($valuesNodes) > 0)
                    {
                        $fieldValue = [];
                        foreach ($valuesNodes as $valueNode)
                        {
                            $fieldValue[] = (string) $valueNode;
                        }
                    }
                    else
                    {
                        $fieldValue = (string) $fieldNode;
                    }

                    $record[$fieldName] = $fieldValue;
                }

                if (count($record) > 0)
                {
                    $result[] = $record;
                }
            }
        }
        catch (Exception $exception)
        {

        }

        return $result;
    }
    /** **********************************************************************
     * get data form xml file
     *
     * @param   array       $data           data
     * @param   SplFileInfo $xmlFile        xml file
     * @return  bool                        writing result
     ************************************************************************/
    private function writeDataToXml(array $data, SplFileInfo $xmlFile) : bool
    {
        $xml        = new DOMDocument;
        $rootNode   = $xml->appendChild($xml->createElement('DOCUMENT'));

        $xml->xmlVersion           = '1.0';
        $xml->encoding             = 'UTF-8';
        $xml->preserveWhiteSpace   = false;
        $xml->formatOutput         = true;

        foreach ($data as $item)
        {
            $itemNode = $rootNode->appendChild($xml->createElement('RECORD'));
            foreach ($item as $index => $value)
            {
                $valueNode = $itemNode->appendChild($xml->createElement($index));

                if (is_array($value))
                {
                    foreach ($value as $subValue)
                    {
                        $subValueNode = $valueNode->appendChild($xml->createElement('VALUE'));
                        $subValueNode->nodeValue = $subValue;
                    }
                }
                else
                {
                    $valueNode->nodeValue = $value;
                }
            }
        }

        return $xml->save($xmlFile->getPathname()) === false ? false : true;
    }
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  Data                        data
     ************************************************************************/
    abstract protected function readProvidedData() : Data;
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   Data $data                  data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function provideDataForDelivery(Data $data) : bool;
}