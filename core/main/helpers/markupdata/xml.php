<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    Exception,
    DOMDocument,
    DOMNode,
    SimpleXMLElement,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * XML structure data class
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class XML extends AbstractData
{
    /** **********************************************************************
     * parse data from string
     *
     * @param   string  $content            file content for parsing
     * @return  array                       data
     ************************************************************************/
    protected function parseData(string $content) : array
    {
        try
        {
            $result = [];
            $xml    = new SimpleXMLElement($content);
            $this->parseXml($xml, $result);

            return is_array($result) ? $result : [];
        }
        catch (Exception $exception)
        {
            return [];
        }
    }
    /** **********************************************************************
     * prepare data for writing into file
     *
     * @param   array       $data           data
     * @return  string                      data for writing
     ************************************************************************/
    protected function prepareDataForWriting(array $data) : string
    {
        $config         = Config::getInstance();
        $rootTagName    = $config->getParam('markup.xml.rootTagName');
        $version        = $config->getParam('markup.xml.version');
        $encoding       = $config->getParam('markup.xml.encoding');
        $xml            = new DOMDocument;
        $rootNode       = $xml->appendChild($xml->createElement($rootTagName));

        $xml->xmlVersion           = $version;
        $xml->encoding             = $encoding;
        $xml->preserveWhiteSpace   = false;
        $xml->formatOutput         = true;

        $this->constructXml($xml, $rootNode, $data);

        return $xml->saveXML();
    }
    /** **********************************************************************
     * parse XML to array
     *
     * @param   SimpleXMLElement    $xml    xml
     * @param   mixed               $data   data
     ************************************************************************/
    private function parseXml(SimpleXMLElement $xml, &$data) : void
    {
        if ($xml->count() > 0)
        {
            if (!is_array($data))
            {
                $data = [];
            }

            foreach ($xml->children() as $child)
            {
                $nodeName = call_user_func([$child, 'getName']);
                $data[$nodeName] = null;
                $this->parseXml($child, $data[$nodeName]);
            }
        }
        else
        {
            $value = (string) $xml;
            if (strlen($value) <= 0)
            {
                $value = null;
            }

            $data = $value;
        }
    }
    /** **********************************************************************
     * construct XML structure based on data array
     *
     * @param   DOMDocument $xml            xml
     * @param   DOMNode     $node           current node
     * @param   mixed       $data           data
     ************************************************************************/
    private function constructXml(DOMDocument $xml, DOMNode $node, $data) : void
    {
        foreach ($data as $key => $value)
        {
            $newNodeName    = is_numeric($key) ? 'item'.$key : $key;
            $newNode        = $node->appendChild($xml->createElement($newNodeName));

            if (is_array($value))
            {
                $this->constructXml($xml, $newNode, $value);
            }
            else
            {
                $newNode->nodeValue = $this->makeValuePrintable($value);
            }
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