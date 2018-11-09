<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    Exception,
    DOMException,
    SplFileInfo,
    DOMDocument,
    DOMNode,
    SimpleXMLElement,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException;
/** ***********************************************************************************************
 * XML structure data class
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class XML implements Data
{
    private const
        DEFAULT_VERSION         = '1.0',
        DEFAULT_ENCODING        = 'UTF-8',
        DEFAULT_ROOT_TAG_NAME   = 'DOCUMENT';
    public
        $version        = '',
        $encoding       = '',
        $rootTagName    = '';
    /** **********************************************************************
     * read from file
     *
     * @param   SplFileInfo $file           file
     * @return  array                       data
     * @throws  ParseDataException          parse data error
     ************************************************************************/
    public function readFromFile(SplFileInfo $file) : array
    {
        $result         = [];
        $fileSize       = $file->getSize();
        $fileContent    = $file->openFile('r')->fread($fileSize);

        try
        {
            libxml_use_internal_errors(true);
            $xml = new SimpleXMLElement($fileContent);
            $this->parseXml($xml, $result);
        }
        catch (Exception $exception)
        {
            throw new ParseDataException($exception->getMessage());
        }

        if (!is_array($result))
        {
            throw new ParseDataException('parse data error');
        }

        return $result;
    }
    /** **********************************************************************
     * read from string
     *
     * @param   string $content             content
     * @return  array                       data
     * @throws  ParseDataException          parse data error
     ************************************************************************/
    public function readFromString(string $content) : array
    {
        $errors = [];
        $result = [];

        try
        {
            libxml_use_internal_errors(true);
            $xml = new SimpleXMLElement($content);
            $this->parseXml($xml, $result);
        }
        catch (Exception $exception)
        {
            $errors[] = $exception->getMessage();
        }

        if (count($errors) <= 0)
        {
            foreach (libxml_get_errors() as $error)
            {
                $errors[] = $error->message;
            }
        }

        if (!is_array($result) && count($errors) <= 0)
        {
            $errors = ['parse data error'];

        }

        if (count($errors) > 0)
        {
            throw new ParseDataException(implode(', ', $errors));
        }

        return $result;
    }
    /** **********************************************************************
     * write to file
     *
     * @param   SplFileInfo $file           file
     * @param   array       $data           data
     * @return  void
     * @throws  WriteDataException          write data error
     ************************************************************************/
    public function writeToFile(SplFileInfo $file, array $data) : void
    {
        try
        {
            $dataForWriting = $this->prepareDataForWriting($data);
            $writtenBytes   = $file->openFile('w')->fwrite($dataForWriting);

            if ($writtenBytes === 0)
            {
                throw new WriteDataException('file was not written with unknown error');
            }
        }
        catch (WriteDataException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * write to string
     *
     * @param   array $data                 data
     * @return  string                      string data
     * @throws  WriteDataException          write data error
     ************************************************************************/
    public function writeToString(array $data) : string
    {
        try
        {
            return $this->prepareDataForWriting($data);
        }
        catch (WriteDataException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * parse XML to array
     *
     * @param   SimpleXMLElement    $xml    xml
     * @param   mixed               $data   data
     * @return  void
     ************************************************************************/
    private function parseXml(SimpleXMLElement $xml, &$data) : void
    {
        if ($xml->count() > 0)
        {
            $data = is_array($data) ? $data : [];

            foreach ($xml->children() as $child)
            {
                $nodeBaseName   = $this->getNodeName($child);
                $nodeName       = $nodeBaseName;
                $nodeIndex      = 1;

                while (array_key_exists($nodeName, $data))
                {
                    $nodeName = $nodeBaseName.($nodeIndex + 1);
                    $nodeIndex++;
                }

                $data[$nodeName] = null;
                $this->parseXml($child, $data[$nodeName]);
            }
        }
        else
        {
            $value  = (string) $xml;
            $data   = strlen($value) > 0 ? $value : null;
        }
    }
    /** **********************************************************************
     * get XML node name
     *
     * @param   SimpleXMLElement $node      xml node
     * @return  string                      node name
     ************************************************************************/
    private function getNodeName(SimpleXMLElement $node) : string
    {
        $nodeName       = $node->getName();
        $nodeAttributes = $node->attributes();

        foreach ($nodeAttributes as $name => $value)
        {
            if ($name == 'name')
            {
                return (string) $value;
            }
        }

        return $nodeName;
    }
    /** **********************************************************************
     * prepare data for writing into file
     *
     * @param   array       $data           data
     * @return  string                      data for writing
     * @throws  WriteDataException          data preparing error
     ************************************************************************/
    private function prepareDataForWriting(array $data) : string
    {
        $xml            = new DOMDocument;
        $version        = is_string($this->version) && strlen($this->version) > 0
            ? $this->version
            : static::DEFAULT_VERSION;
        $encoding       = is_string($this->encoding) && strlen($this->encoding) > 0
            ? $this->encoding
            : static::DEFAULT_ENCODING;
        $rootTagName    = is_string($this->rootTagName) && strlen($this->rootTagName) > 0
            ? $this->rootTagName
            : static::DEFAULT_ROOT_TAG_NAME;
        $xmlData        = [$rootTagName => $data];
        $result         = null;

        try
        {
            $xml->xmlVersion            = $version;
            $xml->encoding              = $encoding;
            $xml->preserveWhiteSpace    = false;
            $xml->formatOutput          = true;
            $xml->validateOnParse       = true;

            $rootNodeName   = (string)  key($xmlData);
            $rootNodeData   = (array)   current($xmlData);
            $rootNode       = $xml->appendChild($xml->createElement($rootNodeName));

            $this->constructXml($xml, $rootNode, $rootNodeData);
            $result = $xml->saveXML();
        }
        catch (DOMException $exception)
        {
            throw new WriteDataException($exception->getMessage());
        }

        return is_string($result) ? $result : '';
    }
    /** **********************************************************************
     * construct XML structure based on data array
     *
     * @param   DOMDocument $xml            xml
     * @param   DOMNode     $node           current node
     * @param   mixed       $data           data
     * @return  void
     * @throws  DOMException                XML constructing error
     ************************************************************************/
    private function constructXml(DOMDocument $xml, DOMNode $node, $data) : void
    {
        try
        {
            foreach ($data as $key => $value)
            {
                $constructedNode    = $this->constructNode($xml, $key);
                $addedNode          = $node->appendChild($constructedNode);

                if (is_array($value))
                {
                    $this->constructXml($xml, $addedNode, $value);
                }
                else
                {
                    $addedNode->nodeValue = $this->makeValuePrintable($value);
                }
            }
        }
        catch (DOMException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * construct XML node
     *
     * @param   DOMDocument $xml            xml
     * @param   mixed       $name           node name
     * @return  DOMNode                     XML node
     * @throws  DOMException                XML node constructing error
     ************************************************************************/
    private function constructNode(DOMDocument $xml, $name) : DOMNode
    {
        $nodeName       = $name;
        $nameAttribute  = '';

        if (is_numeric($nodeName))
        {
            $nodeName = "item-$nodeName";
        }
        if (strpos($nodeName, ' ') !== false)
        {
            $nameAttribute  = $nodeName;
            $nodeName       = str_replace(' ', '-', $nodeName);
        }

        try
        {
            $node = $xml->createElement($nodeName);
            if (strlen($nameAttribute) > 0)
            {
                $node->setAttribute('name', $nameAttribute);
            }

            return $node;
        }
        catch (DOMException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * make value printable
     *
     * @param   mixed   $value              value
     * @return  mixed                       value printable
     ************************************************************************/
    private function makeValuePrintable($value)
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