<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Throwable,
    UnderflowException,
    RuntimeException,
    UnexpectedValueException,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException,
    FilesystemIterator,
    RecursiveDirectoryIterator,
    SplFileInfo,
    Main\Helpers\Logger,
    Main\Helpers\Config,
    Main\Helpers\MarkupData\XLS,
    Main\Helpers\MarkupData\XML;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class Users1C extends AbstractParticipant
{
    private
        $dataFilesRootFolder    = 'usersexchange'.DIRECTORY_SEPARATOR.'1c',
        $receivedFilesFolder    = 'received',
        $returnedFilesFolder    = 'returned',
        $processedFilesFolder   = 'processed';
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        parent::__construct();

        $tempFolderParam            = Config::getInstance()->getParam('structure.tempFolder');
        $this->dataFilesRootFolder  =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $this->dataFilesRootFolder;
    }
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  array                       data
     ************************************************************************/
    protected function readProvidedData() : array
    {
        $result             = [];
        $logger             = Logger::getInstance();
        $logMessagePrefix   = '1C users provided data reading';

        try
        {
            $providedDataFile   = $this->getProvidedDataFile();
            $xlsData            = (new XLS)->readFromFile($providedDataFile);

            foreach ($xlsData as $xlsSheet)
            {
                if (is_array($xlsSheet))
                {
                    $result = array_merge($result, $xlsSheet);
                }
            }

            foreach ($result as $index => $item)
            {
                $result[$index] = $this->convertProvidedItemData($item);
            }

            $this->markProvidedDataFileAsProcessed($providedDataFile);
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: no provided data file was found");
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: provided data parsing error, $error");
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: marking processed file failed, $error");
        }

        return $result;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   array $data                 data to write
     * @return  bool                        process result
     ************************************************************************/
    protected function provideDataForDelivery(array $data) : bool
    {
        $logger             = Logger::getInstance();
        $logMessagePrefix   = '1C users answer data writing';

        try
        {
            $answerFile = $this->getAnswerDataFile();

            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertItemDataForDelivery($item);
            }

            (new XML)->writeToFile($answerFile, $data);
            return true;
        }
        catch (WriteDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: answer data writing error, $error");
            return false;
        }
        catch (UnderflowException $exception)
        {
            $logger->addWarning("$logMessagePrefix: answer file was not created");
            return false;
        }
    }
    /** **********************************************************************
     * get provided XLS data file
     *
     * @return  SplFileInfo                 provided XLS data file
     * @throws  UnderflowException          no data file was found
     ************************************************************************/
    private function getProvidedDataFile() : SplFileInfo
    {
        try
        {
            $receivedDataFilesFolderPath    = $this->dataFilesRootFolder.DIRECTORY_SEPARATOR.$this->receivedFilesFolder;
            $directoryIterator              = new RecursiveDirectoryIterator($receivedDataFilesFolderPath, FilesystemIterator::SKIP_DOTS);

            while ($directoryIterator->valid())
            {
                $file = $directoryIterator->current();

                if ($file->isFile() && $file->isReadable())
                {
                    return $file;
                }

                $directoryIterator->next();
            }
        }
        catch (UnexpectedValueException $exception)
        {

        }

        throw new UnderflowException;
    }
    /** **********************************************************************
     * get answer data file
     *
     * @return  SplFileInfo                 answer data file
     * @throws  UnderflowException          no answer data file was created
     ************************************************************************/
    private function getAnswerDataFile() : SplFileInfo
    {
        $returnedDataFilesFolderPath    = $this->dataFilesRootFolder.DIRECTORY_SEPARATOR.$this->returnedFilesFolder;
        $returnedDataFilesFolder        = new SplFileInfo($returnedDataFilesFolderPath);
        $dataFileNameTemplate           = 'answer_data_{FILE_NUMBER}';
        $dataFileIndex                  = 1;

        if (!$returnedDataFilesFolder->isDir())
        {
            @mkdir($returnedDataFilesFolder->getPathname(), 0777, true);
        }
        if (!$returnedDataFilesFolder->isDir())
        {
            throw new UnderflowException;
        }

        try
        {
            $filesIterator  = new FilesystemIterator($returnedDataFilesFolder->getPathname(), FilesystemIterator::SKIP_DOTS);
            $dataFileIndex += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        while (true)
        {
            $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
            $dataFilePath   = $returnedDataFilesFolder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
            $dataFile       = new SplFileInfo($dataFilePath);

            if (!$dataFile->isFile())
            {
                return $dataFile;
            }

            $dataFileIndex++;
        }

        throw new UnderflowException;
    }
    /** **********************************************************************
     * mark provided data file as processed
     *
     * @param   SplFileInfo $file           provided data file
     * @return  void
     * @throws  RuntimeException            marking provided data file error
     ************************************************************************/
    private function markProvidedDataFileAsProcessed(SplFileInfo $file) : void
    {
        $processedDataFilesFolderPath   = $this->dataFilesRootFolder.DIRECTORY_SEPARATOR.$this->processedFilesFolder;
        $processedDataFilesFolder       = new SplFileInfo($processedDataFilesFolderPath);
        $processedDataFilePath          = $processedDataFilesFolder->getPathname().DIRECTORY_SEPARATOR.$file->getFilename();
        $processedDataFile              = new SplFileInfo($processedDataFilePath);

        if (!$processedDataFilesFolder->isDir())
        {
            @mkdir($processedDataFilesFolder->getPathname(), 0777, true);
        }
        if (!$processedDataFilesFolder->isDir())
        {
            $folderPath = $processedDataFilesFolder->getPathname();
            throw new RuntimeException("creating directory \"$folderPath\" failed");
        }

        $renameResult = @rename($file->getPathname(), $processedDataFile->getPathname());
        if (!$renameResult)
        {
            $movingFrom = $file->getPathname();
            $movingTo   = $processedDataFile->getPathname();

            throw new RuntimeException("moving file from \"$movingFrom\" to \"$movingTo\" failed");
        }
    }
    /** **********************************************************************
     * convert provided item data
     *
     * @param   array $itemData             provided item data
     * @return  array                       converted provided item data
     ************************************************************************/
    private function convertProvidedItemData(array $itemData) : array
    {
        $itemData['Состояние']          =
            array_key_exists('Состояние', $itemData) &&
            $itemData['Состояние'] == 'Работает';
        $itemData['Пол']                =
            array_key_exists('Пол', $itemData) &&
            $itemData['Пол'] == 'Мужской'
                ? 'M'
                : 'F';
        $itemData['ИД пользователя 1С'] =
            array_key_exists('ГУИД', $itemData)
                ? $itemData['ГУИД']
                : null;

        return $itemData;
    }
    /** **********************************************************************
     * convert item data for delivery
     *
     * @param   array $itemData             item data for delivery
     * @return  array                       converted item data for delivery
     ************************************************************************/
    private function convertItemDataForDelivery(array $itemData) : array
    {
        $itemData['Состояние']  =
            array_key_exists('Состояние', $itemData) &&
            $itemData['Состояние'] === true
                ? 'Работает'
                : 'Уволен';
        $itemData['Пол']        =
            array_key_exists('Пол', $itemData) &&
            $itemData['Пол'] == 'M'
                ? 'Мужской'
                : 'Женский';
        unset($itemData['ИД пользователя 1С']);

        return $itemData;
    }
}