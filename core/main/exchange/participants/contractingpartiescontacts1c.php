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
    Main\Helpers\MarkupData\XML;
/** ***********************************************************************************************
 * Application participant ContractingPartiesContacts1C
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ContractingPartiesContacts1C extends AbstractParticipant
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  array                       data
     ************************************************************************/
    protected function readProvidedData() : array
    {
        $logger             = Logger::getInstance();
        $logMessagePrefix   = '1C contractingparties contacts provided data reading';

        try
        {
            $providedDataFile   = $this->getProvidedDataFile();
            $data               = (new XML)->readFromFile($providedDataFile);

            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertProvidedItemData($item);
            }

            $this->markProvidedDataFileAsProcessed($providedDataFile);

            return $data;
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: no provided data file was found");
            return [];
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: provided data parsing error, $error");
            return [];
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: marking processed file failed, $error");
            return [];
        }
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
        $logMessagePrefix   = '1C contractingparties contacts providing data for delivery';
echo '<pre>';
print_r($data);
echo '</pre>';
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
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $receivedDataFolderParam    = $config->getParam('participants.contractingparties.contacts.1c.receivedDataPath');
        $receivedDataFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $receivedDataFolderParam;
        $receivedDataFolder         = new SplFileInfo($receivedDataFolderPath);

        if (!$receivedDataFolder->isDir())
        {
            @mkdir($receivedDataFolder->getPathname(), 0777, true);
        }

        try
        {
            $directoryIterator = new RecursiveDirectoryIterator
            (
                $receivedDataFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );

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
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $returnedDataFolderParam    = $config->getParam('participants.contractingparties.contacts.1c.returnedDataPath');
        $returnedDataFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $returnedDataFolderParam;
        $returnedDataFolder         = new SplFileInfo($returnedDataFolderPath);
        $dataFileNameTemplate       = 'answer_data_{FILE_NUMBER}';
        $dataFileIndex              = 1;

        if (!$returnedDataFolder->isDir())
        {
            @mkdir($returnedDataFolder->getPathname(), 0777, true);
        }
        if (!$returnedDataFolder->isDir())
        {
            throw new UnderflowException;
        }

        try
        {
            $filesIterator  = new FilesystemIterator
            (
                $returnedDataFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );
            $dataFileIndex += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        while (true)
        {
            $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
            $dataFilePath   = $returnedDataFolder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
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
     * @throws  RuntimeException            marking provided data file error
     ************************************************************************/
    private function markProvidedDataFileAsProcessed(SplFileInfo $file) : void
    {
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $processedDataFolderParam   = $config->getParam('participants.contractingparties.contacts.1c.processedDataPath');
        $processedDataFolderPath    =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $processedDataFolderParam;
        $processedDataFolder        = new SplFileInfo($processedDataFolderPath);
        $processedDataFilePath      = $processedDataFolder->getPathname().DIRECTORY_SEPARATOR.$file->getFilename();
        $processedDataFile          = new SplFileInfo($processedDataFilePath);

        if (!$processedDataFolder->isDir())
        {
            @mkdir($processedDataFolder->getPathname(), 0777, true);
        }
        if (!$processedDataFolder->isDir())
        {
            $folderPath = $processedDataFolder->getPathname();
            throw new RuntimeException("unable to create folder \"$folderPath\"");
        }

        try
        {
            rename($file->getPathname(), $processedDataFile->getPathname());
        }
        catch (Throwable $exception)
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
        return $itemData;
    }
}