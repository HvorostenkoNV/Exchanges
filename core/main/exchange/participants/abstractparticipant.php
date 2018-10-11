<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    UnexpectedValueException,
    InvalidArgumentException,
    ReflectionException,
    Main\Helpers\Database\Exceptions\ConnectionException    as DBConnectionException,
    Main\Helpers\Database\Exceptions\QueryException         as DBQueryException,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException,
    ReflectionClass,
    SplFileInfo,
    FilesystemIterator,
    RecursiveDirectoryIterator,
    Main\Data\MapData,
    Main\Helpers\Database\DB,
    Main\Helpers\Logger,
    Main\Helpers\Config,
    Main\Helpers\MarkupData\XML,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Fields\FieldsSet,
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Application participant abstract class
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipant implements Participant
{
    private
        $code                   = '',
        $fieldsCollection       = null,
        $idField                = null,
        $currentProcessDataFile = '';
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

        try
        {
            $this->addLogMessage('fields collection constructing start', 'notice');

            $this->fieldsCollection = $this->constructFieldsCollection($this->code);

            $this->addLogMessage('fields collection constructed', 'notice');
            if ($this->fieldsCollection->count() <= 0)
            {
                $this->addLogMessage('fields collection is empty', 'warning');
            }
        }
        catch (RuntimeException $exception)
        {
            $this->fieldsCollection = new FieldsSet;

            $error = $exception->getMessage();
            $this->addLogMessage("fields collection constructing error, \"$error\"", 'warning');
        }

        $this->fieldsCollection->rewind();
        while ($this->fieldsCollection->valid())
        {
            $field = $this->fieldsCollection->current();
            if ($field->getParam('type') == FieldsTypesManager::ID_FIELD_TYPE)
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
        $code               = $this->getCode();
        $fields             = $this->getFields();
        $preparedDataFile   = $this->getPreparedDataFile($code);

        $this->addLogMessage('provided data constructing start', 'notice');

        if ($preparedDataFile)
        {
            try
            {
                $this->addLogMessage('prepared data file found', 'notice');
                $this->addLogMessage('prepared data file extracting start', 'notice');

                $dataArray  = (new XML)->readFromFile($preparedDataFile);
                $data       = $this->constructData($dataArray, $fields);
                $data       = $this->validateData($data);

                $this->addLogMessage('prepared data collected', 'notice');
                if ($data->count() <= 0)
                {
                    $this->addLogMessage('prepared data is empty', 'warning');
                }

                $this->currentProcessDataFile = $preparedDataFile->getPathname();
                return $data;
            }
            catch (ParseDataException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("prepared data extracting error, $error", 'warning');
            }
        }
        else
        {
            try
            {
                $this->addLogMessage('provided data collecting start', 'notice');

                $dataArray = $this->readProvidedData();

                $this->addLogMessage('provided data preparing start', 'notice');

                $this->saveProvidedData($code, $dataArray);
            }
            catch (RuntimeException $exception)
            {
                $error = $exception->getMessage();
                $this->addLogMessage("gathered provided data saving error, $error", 'warning');
            }
        }

        return new ProvidedData;
    }
    /** **********************************************************************
     * delivery data to the participant
     *
     * @param   Data $data                  data for delivery
     * @return  bool                        delivering data result
     ************************************************************************/
    final public function deliveryData(Data $data) : bool
    {
        $dataArray = [];

        if ($data->count() <= 0)
        {
            $this->addLogMessage('data for delivery is empty', 'notice');
        }
        $this->addLogMessage('data delivering process run', 'notice');

        while (!$data->isEmpty())
        {
            try
            {
                $item       = $data->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $field)
                {
                    $value      = $item->get($field);
                    $fieldName  = $field->getParam('name');
                    $itemArray[$fieldName] = $value;
                }

                $dataArray[] = $itemArray;
            }
            catch (RuntimeException $exception)
            {

            }
        }

        if (strlen($this->currentProcessDataFile) > 0)
        {
            unlink($this->currentProcessDataFile);
        }

        return $this->provideDataForDelivery($dataArray);
    }
    /** **********************************************************************
     * construct participant fields collection
     *
     * @param   string $code                participant code
     * @return  FieldsSet                   participant fields collection
     * @throws  RuntimeException            participant fields collection constructing error
     ************************************************************************/
    private function constructFieldsCollection(string $code) : FieldsSet
    {
        $result         = new FieldsSet;
        $queryResult    = null;

        try
        {
            $queryResult = $this->queryFieldsInfo($code);
        }
        catch (DBConnectionException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (DBQueryException $exception)
        {
            throw new RuntimeException($exception->getMessage());
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
     * get prepared data file
     *
     * @param   string $code                participant code
     * @return  SplFileInfo|null            prepared data file
     ************************************************************************/
    private function getPreparedDataFile(string $code) : ?SplFileInfo
    {
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $preparedDataFolderParam    = $config->getParam('exchange.preparedDataFolder');
        $dataFilesFolderPath        =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $preparedDataFolderParam.DIRECTORY_SEPARATOR.
            $code;

        try
        {
            $directoryIterator = new RecursiveDirectoryIterator
            (
                $dataFilesFolderPath,
                FilesystemIterator::SKIP_DOTS
            );

            while ($directoryIterator->valid())
            {
                $file = $directoryIterator->current();

                if ($file->isFile() && $file->isReadable() && $file->getExtension() == 'xml')
                {
                    return $file;
                }

                $directoryIterator->next();
            }
        }
        catch (UnexpectedValueException $exception)
        {

        }

        return null;
    }
    /** **********************************************************************
     * construct data from data array
     *
     * @param   array       $dataArray      data array
     * @param   FieldsSet   $fields         participant fields collection
     * @return  Data                        extracted data
     ************************************************************************/
    private function constructData(array $dataArray, FieldsSet $fields) : Data
    {
        $result = new ProvidedData;

        foreach ($dataArray as $item)
        {
            if (is_array($item))
            {
                $map = new ItemData;

                foreach ($item as $key => $value)
                {
                    try
                    {
                        $field = $fields->findField($key);
                        $map->set($field, $value);
                    }
                    catch (InvalidArgumentException $exception)
                    {

                    }
                }

                try
                {
                    $result->push($map);
                }
                catch (InvalidArgumentException $exception)
                {

                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * save provided data
     *
     * @param   string  $code               participant code
     * @param   array   $data               provided data
     * @throws  RuntimeException            provided data saving error
     ************************************************************************/
    private function saveProvidedData(string $code, array $data) : void
    {
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $dataChunkSizeParam         = (int) $config->getParam('exchange.providedDataChunkSize');
        $preparedDataFolderParam    = $config->getParam('exchange.preparedDataFolder');
        $dataFilesFolderPath        =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $preparedDataFolderParam.DIRECTORY_SEPARATOR.
            $code;
        $dataFilesFolder            = new SplFileInfo($dataFilesFolderPath);
        $dataChunkSize              = $dataChunkSizeParam > 0 ? $dataChunkSizeParam : 10;
        $partedData                 = array_chunk($data, $dataChunkSize);
        $dataFileNameTemplate       = 'prepared_data_{FILE_NUMBER}';
        $dataFileIndex              = 1;

        if (!$dataFilesFolder->isDir())
        {
            @mkdir($dataFilesFolder->getPathname(), 0777, true);
        }
        if (!$dataFilesFolder->isDir())
        {
            throw new RuntimeException("\"$dataFilesFolderPath\" folder not exist and cannot be created");
        }

        try
        {
            $filesIterator  = new FilesystemIterator
            (
                $dataFilesFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );
            $dataFileIndex += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        foreach ($partedData as $partData)
        {
            $dataFile = null;

            while (!$dataFile)
            {
                $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
                $dataFilePath   = $dataFilesFolder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
                $dataFile       = new SplFileInfo($dataFilePath);

                if ($dataFile->isFile())
                {
                    $dataFile = null;
                    $dataFileIndex++;
                }
            }

            try
            {
                (new XML())->writeToFile($dataFile, $partData);
            }
            catch (WriteDataException $exception)
            {
                throw new RuntimeException($exception->getMessage());
            }
        }
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
     * @param   string $code                participant code
     * @return  array                       query result
     * @throws  DBConnectionException       db connection error
     * @throws  DBQueryException            db query error
     ************************************************************************/
    private function queryFieldsInfo(string $code) : array
    {
        $result         = [];
        $sqlQuery       = '
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
        $queryResult    = null;

        try
        {
            $queryResult = DB::getInstance()->query($sqlQuery, [$code]);
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
                $item       = $queryResult->pop();
                $itemArray  = [];
                foreach ($item->getKeys() as $key)
                {
                    $itemArray[$key] = $item->get($key);
                }
                $result[] = $itemArray;
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
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
     * @return  array                       data
     ************************************************************************/
    abstract protected function readProvidedData() : array;
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   array $data                 data to write
     * @return  bool                        process result
     ************************************************************************/
    abstract protected function provideDataForDelivery(array $data) : bool;
}