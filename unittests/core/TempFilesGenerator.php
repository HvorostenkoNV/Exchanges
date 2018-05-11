<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    Throwable,
    RuntimeException,
    InvalidArgumentException,
    ReflectionException,
    UnexpectedValueException,
    ReflectionClass,
    SplFileInfo,
    RecursiveDirectoryIterator,
    DOMDocument;
/** ***********************************************************************************************
 * Class for creating/deleting application temp files
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class TempFilesGenerator
{
    private static $tempXmlFilesFolder = 'unit_test_temp_xml';
    private $tempFiles = [];
    /** **********************************************************************
     * create temp class file based on other class file
     * using for creating family temp class file
     *
     * @param   string  $baseClassName          base class full name
     * @param   string  $newClassName           new class short name
     * @return  bool                            creating success
     ************************************************************************/
    public function createTempClass(string $baseClassName, string $newClassName) : bool
    {
        $baseClassReflection    = null;
        $baseClassFile          = null;
        $baseClassContent       = '';

        try
        {
            $baseClassReflection    = new ReflectionClass($baseClassName);
            $baseClassFile          = new SplFileInfo($baseClassReflection->getFileName());

            if ($baseClassFile->isFile() && $baseClassFile->isReadable())
            {
                $baseClassContent = $baseClassFile->openFile('r')->fread($baseClassFile->getSize());
            }
        }
        catch (ReflectionException $exception)
        {
            return false;
        }

        $newClassFileName       = $newClassName.'.php';
        $newClassFilePath       = $baseClassFile->getPath().DIRECTORY_SEPARATOR.$newClassFileName;
        $newClassFile           = new SplFileInfo($newClassFilePath);
        $newClassQualifiedName  = $baseClassReflection->getNamespaceName().'\\'.$newClassName;
        $newClassFileContent    = str_replace($baseClassReflection->getShortName(), $newClassName, $baseClassContent);

        if (!$newClassFile->isFile())
        {
            $newClassFile
                ->openFile('w')
                ->fwrite($newClassFileContent);
        }

        try
        {
            include $newClassFile->getPathname();
            new $newClassQualifiedName;
            $this->tempFiles[] = $newClassFile->getPathname();
            return true;
        }
        catch (Throwable $exception)
        {
            return false;
        }
    }
    /** **********************************************************************
     * create temp class file based on other class file
     * using for creating family temp class file
     *
     * @param   array   $info                   xml info
     * @return  SplFileInfo|null                new xml file
     ************************************************************************/
    public function createTempXml(array $info) : ?SplFileInfo
    {
        $tempXmlFilesFolder = new SplFileInfo($_SERVER['DOCUMENT_ROOT'].DS.self::$tempXmlFilesFolder);

        if (!$tempXmlFilesFolder->isDir())
        {
            mkdir($tempXmlFilesFolder->getPathname());
        }

        try
        {
            $newXmlFilePath = $this->generateNewTempXmlPath($tempXmlFilesFolder);
            $xml            = $this->generateNewTempXmlStructure($info);
            $xml->save($newXmlFilePath);

            return new SplFileInfo($newXmlFilePath);
        }
        catch (RuntimeException $exception)
        {
            return null;
        }
        catch (InvalidArgumentException $exception)
        {
            return null;
        }
    }
    /** **********************************************************************
     * drop created temp data
     ************************************************************************/
    public function dropCreatedTempData() : void
    {
        $this->dropCreatedTempFiles();
        $this->dropCreatedTempXml();
    }
    /** **********************************************************************
     * get generated new temp XML file path
     *
     * @param   SplFileInfo $folder             xml folder
     * @return  string                          new xml file path
     * @throws  RuntimeException                xml folder reading problems
     ************************************************************************/
    private function generateNewTempXmlPath(SplFileInfo $folder) : string
    {
        try
        {
            $filesCount = 0;
            $iterator   = new RecursiveDirectoryIterator($folder->getPathname());

            while ($iterator->valid())
            {
                if ($iterator->current()->isFile())
                {
                    $filesCount++;
                }
                $iterator->next();
            }

            return $folder->getPathname().DS.'tempXmlFile'.($filesCount + 1).'.xml';
        }
        catch (UnexpectedValueException|RuntimeException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * get generated new temp XML file path
     *
     * @param   array   $info                   xml info
     * @return  DOMDocument                     new xml structure
     * @throws  InvalidArgumentException        xml info array is empty
     ************************************************************************/
    private function generateNewTempXmlStructure(array $info) : DOMDocument
    {
        if (count($info) <= 0)
        {
            throw new InvalidArgumentException('info array is empty');
        }

        $document   = new DOMDocument;
        $rootNode   = $document->appendChild($document->createElement('DOCUMENT'));

        $document->xmlVersion           = '1.0';
        $document->encoding             = 'UTF-8';
        $document->preserveWhiteSpace   = false;
        $document->formatOutput         = true;

        foreach ($info as $item)
        {
            $itemNode = $rootNode->appendChild($document->createElement('RECORD'));
            foreach ($item as $index => $value)
            {
                $valueNode = $itemNode->appendChild($document->createElement($index));

                if (is_array($value))
                {
                    foreach ($value as $subValue)
                    {
                        $subValueNode = $valueNode->appendChild($document->createElement('VALUE'));
                        $subValueNode->nodeValue = $this->getXmlNodePrintableValue($subValue);
                    }
                }
                else
                {
                    $valueNode->nodeValue = $this->getXmlNodePrintableValue($value);
                }
            }
        }

        return $document;
    }
    /** **********************************************************************
     * get new temp XML file path
     *
     * @param   mixed   $value                  value
     * @return  mixed   $value                  value
     ************************************************************************/
    private function getXmlNodePrintableValue($value)
    {
        switch (gettype($value))
        {
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                return $value;
            case 'boolean':
                return $value ? 'Y' : 'N';
            case 'array':
            case 'object':
            case 'resource':
            default:
                return null;
        }
    }
    /** **********************************************************************
     * drop created temp files
     ************************************************************************/
    private function dropCreatedTempFiles() : void
    {
        foreach ($this->tempFiles as $filePath)
        {
            $file = new SplFileInfo($filePath);
            if ($file->isFile())
            {
                unlink($file->getPathname());
            }
        }
    }
    /** **********************************************************************
     * drop created temp xml
     ************************************************************************/
    private function dropCreatedTempXml() : void
    {
        $tempXmlFilesFolder = new SplFileInfo($_SERVER['DOCUMENT_ROOT'].DS.self::$tempXmlFilesFolder);
        if ($tempXmlFilesFolder->isDir())
        {
            $iterator = new RecursiveDirectoryIterator($tempXmlFilesFolder->getPathname());

            while ($iterator->valid())
            {
                if ($iterator->current()->isFile())
                {
                    unlink($iterator->current()->getPathname());
                }
                $iterator->next();
            }

            rmdir($tempXmlFilesFolder->getPathname());
        }
    }
}