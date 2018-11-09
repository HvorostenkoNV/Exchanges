<?php
declare(strict_types=1);

use
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException,
    Main\Helpers\Config,
    Main\Helpers\MarkupData\XML;
/** ***********************************************************************************************
 * 1c contractingparties exchange entrance file
 *
 * @package exchange_public
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';
/** ***********************************************************************************************
 * variables
 *************************************************************************************************/
$requestMethod                      = $_SERVER['REQUEST_METHOD'];
$requestMode                        = $_GET['mode'] ?? '';
$config                             = Config::getInstance();
$tempFolderParam                    = $config->getParam('structure.tempFolder');
$getedDataFolderParam               = $config->getParam('participants.contractingparties.companies.1c.implodedReceivedDataPath');
$returnedDataForDeliveryFolderParam = $config->getParam('participants.contractingparties.companies.1c.implodedReturnedDataPath');
$readyDataForDeliveryFolderParam    = $config->getParam('participants.contractingparties.companies.1c.implodedPreparedDataPath');
$tempFolderPath                     = DOCUMENT_ROOT.DIRECTORY_SEPARATOR.$tempFolderParam;
$getedDataFolder                    = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$getedDataFolderParam);
$returnedDataForDeliveryFolder      = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$returnedDataForDeliveryFolderParam);
$readyDataForDeliveryFolder         = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$readyDataForDeliveryFolderParam);
$exchangeFile                       = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.'1cContractingpartiesExchange.xml');
/** ***********************************************************************************************
 * request type calc
 *************************************************************************************************/
if ($requestMethod == 'GET')
{
    switch ($requestMode)
    {
        case 'checkauth':
            $requestMethod = 'AUTH';
            break;
        case 'init':
            $requestMethod = 'INIT';
            break;
        case 'import':
            $requestMethod = 'POST_END';
            break;
    }
}
/** ***********************************************************************************************
 * get XML files from folder function
 *
 * @param   SplFileInfo $folder             folder
 * @return  SplFileInfo[]                   XML files
 *************************************************************************************************/
$getXmlFilesFromFolder = function(SplFileInfo $folder) : array
{
    $files = [];

    try
    {
        $skipDotsParam  = FilesystemIterator::SKIP_DOTS;
        $folderIterator = new RecursiveDirectoryIterator($folder->getPathname(), $skipDotsParam);

        while ($folderIterator->valid())
        {
            $file = $folderIterator->current();

            if ($file->isFile() && $file->isReadable() && $file->getExtension() == 'xml')
            {
                $files[] = $file;
            }

            $folderIterator->next();
        }
    }
    catch (UnexpectedValueException $exception)
    {

    }

    return $files;
};
/** ***********************************************************************************************
 * replace files to folder function
 *
 * @param   SplFileInfo[]   $files          files
 * @param   SplFileInfo     $folder         folder
 * @return  bool                            replacing success
 *************************************************************************************************/
$replaceFilesToFolder = function(array $files, SplFileInfo $folder) : bool
{
    $result             = true;
    $getNewFileInFolder = function(SplFileInfo $searchFolder) : ?SplFileInfo
    {
        $fileNameTemplate   = 'data_file_{FILE_NUMBER}';
        $fileIndex          = 1;

        try
        {
            $skipDotsParam  = FilesystemIterator::SKIP_DOTS;
            $filesIterator  = new FilesystemIterator($searchFolder->getPathname(), $skipDotsParam);
            $fileIndex     += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        while (true)
        {
            $fileName   = str_replace('{FILE_NUMBER}', $fileIndex, $fileNameTemplate);
            $filePath   = $searchFolder->getPathname().DIRECTORY_SEPARATOR.$fileName.'.xml';
            $file       = new SplFileInfo($filePath);

            if (!$file->isFile())
            {
                return $file;
            }

            $fileIndex++;
        }

        return null;
    };

    if (!$folder->isDir())
    {
        @mkdir($folder->getPathname(), 0777, true);
    }

    foreach ($files as $file)
    {
        $newFile            = $getNewFileInFolder($folder);
        $replacingSuccess   = @rename($file->getPathname(), $newFile->getPathname());

        if (!$replacingSuccess)
        {
            $result = false;
        }
    }

    return $result;
};
/** ***********************************************************************************************
 * get combined XML data from XML files function
 *
 * @param   SplFileInfo[] $files            XML files
 * @return  string                          combined XML data
 *************************************************************************************************/
$getCombinedXmlDataFromFiles = function(array $files) : string
{
    $data = [];

    foreach ($files as $file)
    {
        try
        {
            $fileData = (new XML)->readFromFile($file);

            foreach ($fileData as $itemData)
            {
                $itemDataSerialize          = serialize($itemData);
                $data[$itemDataSerialize]   = $itemData;
            }
        }
        catch (ParseDataException $exception)
        {

        }
    }

    try
    {
        $xml                = new XML;
        $xml->rootTagName   = 'КоммерческаяИнформация';
        $data               = ['Контрагенты' => array_values($data)];

        return $xml->writeToString($data);
    }
    catch (WriteDataException $exception)
    {
        return '';
    }
};
/** ***********************************************************************************************
 * handles
 *************************************************************************************************/
switch ($requestMethod)
{
    /** **********************************************************************
     * authentication
     ************************************************************************/
    case 'AUTH':
        echo 'success';
        break;
    /** **********************************************************************
     * init return
     ************************************************************************/
    case 'INIT':
        echo "no\n";
        echo "file_limit=10000\n";
        echo "123456789\n";
        echo "version=2.09\n";
        break;
    /** **********************************************************************
     * read data
     ************************************************************************/
    case 'GET':
        $readyFiles = $getXmlFilesFromFolder($readyDataForDeliveryFolder);

        if (count($readyFiles) <= 0)
        {
            $returnedFiles  = $getXmlFilesFromFolder($returnedDataForDeliveryFolder);
            $returnedFiles  = count($returnedFiles) > 5 ? array_slice($returnedFiles, 0, 5) : $returnedFiles;
            $replaceFilesToFolder($returnedFiles, $readyDataForDeliveryFolder);
            $readyFiles = $getXmlFilesFromFolder($readyDataForDeliveryFolder);
        }

        header('Content-type: text/xml');
        echo $getCombinedXmlDataFromFiles($readyFiles);
        break;
    /** **********************************************************************
     * write data
     ************************************************************************/
    case 'POST':
        $caughtData = @file_get_contents('php://input');

        if (!$exchangeFile->isFile())
        {
            $exchangeFile->openFile('w')->fwrite('');
        }
        if (!$exchangeFile->isFile())
        {
            exit("failure\nTemp file creating failed");
        }
        if (!is_string($caughtData) || strlen($caughtData) <= 0)
        {
            exit("failure\nCaught no data");
        }

        $fileWritingResult = $exchangeFile->openFile('a')->fwrite($caughtData);
        if ($fileWritingResult <= 0)
        {
            exit("failure\nData writing failed");
        }

        try
        {
            (new XML)->readFromFile($exchangeFile);

            $fileReplacingResult = $replaceFilesToFolder([$exchangeFile], $getedDataFolder);
            if (!$fileReplacingResult)
            {
                exit("failure\nComplete data saving failed");
            }
        }
        catch (ParseDataException $exception)
        {

        }

        echo 'success';
        break;
    /** **********************************************************************
     * write data end
     ************************************************************************/
    case 'POST_END':
        if ($exchangeFile->isFile())
        {
            @unlink($exchangeFile->getPathname());
        }

        echo 'success';
        break;
    default:
}