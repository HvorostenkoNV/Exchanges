<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    Throwable,
    RuntimeException,
    ReflectionException,
    UnexpectedValueException,
    ReflectionClass,
    SplFileInfo,
    RecursiveDirectoryIterator,
    DOMDocument,
    DOMNode,
    Main\Helpers\Config;
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
     * create temp XML
     *
     * @param   array   $data                   xml data
     * @return  SplFileInfo|null                new xml file
     ************************************************************************/
    public function createTempXml(array $data) : ?SplFileInfo
    {
        $tempXmlFilesFolder = new SplFileInfo($_SERVER['DOCUMENT_ROOT'].DS.self::$tempXmlFilesFolder);

        if (!$tempXmlFilesFolder->isDir())
        {
            mkdir($tempXmlFilesFolder->getPathname());
        }

        try
        {
            $newXmlFilePath = $this->generateNewTempXmlPath($tempXmlFilesFolder);
            $xml            = $this->generateNewTempXmlStructure($data);
            $xml->save($newXmlFilePath);

            return new SplFileInfo($newXmlFilePath);
        }
        catch (RuntimeException $exception)
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
        catch (UnexpectedValueException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get generated new temp XML file path
     *
     * @param   array   $data                   xml info
     * @return  DOMDocument                     new xml structure
     ************************************************************************/
    private function generateNewTempXmlStructure(array $data) : DOMDocument
    {
        $config         = Config::getInstance();
        $rootTagName    = $config->getParam('markup.xml.rootTagName');
        $version        = $config->getParam('markup.xml.version');
        $encoding       = $config->getParam('markup.xml.encoding');
        $document       = new DOMDocument;
        $rootNode       = $document->appendChild($document->createElement($rootTagName));

        $document->xmlVersion           = $version;
        $document->encoding             = $encoding;
        $document->preserveWhiteSpace   = false;
        $document->formatOutput         = true;

        $this->buildXmlStructure($document, $rootNode, $data);

        return $document;
    }
    /** **********************************************************************
     * get generated new temp XML file path
     *
     * @param   DOMDocument $document           xml document
     * @param   DOMNode     $node               xml node
     * @param   array       $data               data
     ************************************************************************/
    private function buildXmlStructure(DOMDocument $document, DOMNode $node, array $data) : void
    {
        foreach ($data as $key => $value)
        {
            $newNodeName    = is_numeric($key) ? 'item'.$key : $key;
            $newNode        = $node->appendChild($document->createElement($newNodeName));

            if (is_array($value))
            {
                $this->buildXmlStructure($document, $newNode, $value);
            }
            else
            {
                $newNode->nodeValue = $this->getValuePrintable($value);
            }
        }
    }
    /** **********************************************************************
     * get value printable
     *
     * @param   mixed   $value                  value
     * @return  mixed                           value printable
     ************************************************************************/
    private function getValuePrintable($value)
    {
        switch (gettype($value))
        {
            case 'string':
                return $value;
            case 'boolean':
                return $value ? 'Y' : 'N';
            default:
                return (string) $value;
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