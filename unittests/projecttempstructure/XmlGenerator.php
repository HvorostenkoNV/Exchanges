<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    RuntimeException,
    UnexpectedValueException,
    SplFileInfo,
    RecursiveDirectoryIterator,
    DOMDocument,
    DOMNode;
/** ***********************************************************************************************
 * Class for creating/deleting application temp files
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class XmlGenerator
{
    private static $tempXmlFolder = 'unit_test_temp_xml';
    /** **********************************************************************
     * create temp XML
     *
     * @param   array   $data               xml data
     * @return  SplFileInfo                 new xml file
     * @throws  RuntimeException            creating error
     ************************************************************************/
    public function createXml(array $data) : SplFileInfo
    {
        $tempXmlFolder = new SplFileInfo($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.self::$tempXmlFolder);

        if (!$tempXmlFolder->isDir())
        {
            @mkdir($tempXmlFolder->getPathname(), 0777, true);
        }
        if (!$tempXmlFolder->isDir())
        {
            $folderPath = $tempXmlFolder->getPathname();
            throw new RuntimeException("creating directory \"$folderPath\" failed");
        }

        try
        {
            $newXmlFile     = $this->createNewEmptyFile($tempXmlFolder);
            $xml            = $this->generateNewXmlStructure($data);
            $savingResult   = $xml->save($newXmlFile->getPathname());

            if ($savingResult === false)
            {
                throw new RuntimeException('saving temp XM error');
            }

            return $newXmlFile;
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * drop created temp XML files
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        $tempXmlFolder = new SplFileInfo($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.self::$tempXmlFolder);

        if ($tempXmlFolder->isDir())
        {
            $iterator = new RecursiveDirectoryIterator($tempXmlFolder->getPathname());

            while ($iterator->valid())
            {
                if ($iterator->current()->isFile())
                {
                    unlink($iterator->current()->getPathname());
                }
                $iterator->next();
            }

            rmdir($tempXmlFolder->getPathname());
        }
    }
    /** **********************************************************************
     * create new empty XML file
     *
     * @param   SplFileInfo $folder             root folder
     * @return  SplFileInfo                     new xml file
     * @throws  RuntimeException                xml folder reading problems
     ************************************************************************/
    private function createNewEmptyFile(SplFileInfo $folder) : SplFileInfo
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

            $newFileName = $folder->getPathname().DIRECTORY_SEPARATOR.'tempXmlFile'.($filesCount + 1).'.xml';
            return new SplFileInfo($newFileName);
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
    private function generateNewXmlStructure(array $data) : DOMDocument
    {
        $rootTagName    = $GLOBALS['XML_ROOT_TAG'];
        $version        = $GLOBALS['XML_VERSION'];
        $encoding       = $GLOBALS['XML_ENCODING'];
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
     * @return  void
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
}