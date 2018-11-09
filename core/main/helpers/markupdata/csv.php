<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    Exception,
    SplFileInfo,
    Main\Helpers\Config,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException;
/** ***********************************************************************************************
 * CSV structure data class
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class CSV implements Data
{
    private static
        $cellsDelimiter = '',
        $rowsDelimiter  = '';
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $config = Config::getInstance();

        if (strlen(self::$cellsDelimiter) <= 0)
        {
            self::$cellsDelimiter = $config->getParam('markup.csv.cellsDelimiter');
        }
        if (strlen(self::$cellsDelimiter) <= 0)
        {
            self::$cellsDelimiter = ',';
        }

        if (strlen(self::$rowsDelimiter) <= 0)
        {
            self::$rowsDelimiter = $config->getParam('markup.csv.rowsDelimiter');
        }
        if (strlen(self::$rowsDelimiter) <= 0)
        {
            self::$rowsDelimiter = ';';
        }
    }
    /** **********************************************************************
     * read from file
     *
     * @param   SplFileInfo $file           file
     * @return  array                       data
     * @throws  ParseDataException          parse data error
     ************************************************************************/
    public function readFromFile(SplFileInfo $file) : array
    {
        if ($file->getExtension() != 'csv')
        {
            throw new ParseDataException('wrong file extension');
        }

        try
        {
            $filePath       = $file->getPathname();
            $fileContent    = file_get_contents($filePath);

            if (!is_string($fileContent))
            {
                throw new Exception("reading file \"$filePath\" failed with unknown error");
            }

            return $this->readFromString($fileContent);
        }
        catch (Exception $exception)
        {
            throw new ParseDataException($exception->getMessage());
        }
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
        $preparedContent    = str_replace
        (
            [
                self::$rowsDelimiter."\n",
                self::$rowsDelimiter."\r"
            ],
            self::$rowsDelimiter,
            trim($content)
        );
        $rows               = explode(self::$rowsDelimiter, $preparedContent);
        $data               = [];

        foreach ($rows as $rowData)
        {
            $data[] = explode(self::$cellsDelimiter, $rowData);
        }

        return $this->convertReadData($data);
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
        if ($file->getExtension() != 'csv')
        {
            throw new WriteDataException('wrong file extension');
        }

        $convertedData  = $this->convertDataForWriting($data);
        $handle         = fopen($file->getPathname(), 'w');

        foreach ($convertedData as $rowData)
        {
            $rowDataString      = implode(self::$cellsDelimiter, $rowData).self::$rowsDelimiter."\n";
            $writingRowSuccess  = fwrite($handle, $rowDataString);

            if ($writingRowSuccess === false)
            {
                throw new WriteDataException('CSV file writing failed with unknown error');
            }
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
        $convertedData  = $this->convertDataForWriting($data);
        $result         = '';

        foreach ($convertedData as $rowData)
        {
            $result .= implode(self::$cellsDelimiter, $rowData).self::$rowsDelimiter."\n";
        }

        return $result;
    }
    /** **********************************************************************
     * convert already read data
     *
     * @param   array $data                 data
     * @return  array                       converted data
     ************************************************************************/
    private function convertReadData(array $data) : array
    {
        $result     = [];
        $headers    = array_shift($data);

        if (is_null($headers))
        {
            return [];
        }

        foreach ($data as $rowData)
        {
            $itemData = [];
            foreach ($headers as $index => $header)
            {
                $itemData[$header] = array_key_exists($index, $rowData)
                    ? $rowData[$index]
                    : null;
            }
            $result[] = $itemData;
        }

        return $result;
    }
    /** **********************************************************************
     * convert data for writing CSV
     *
     * @param   array $data                 data
     * @return  array                       converted data
     ************************************************************************/
    private function convertDataForWriting(array $data) : array
    {
        $result     = [];
        $headers    = [];

        foreach ($data as $item)
        {
            if (is_array($item))
            {
                $headers = array_merge($headers, array_keys($item));
            }
        }
        $headers = array_unique($headers);

        $result[] = array_values($headers);
        foreach ($data as $item)
        {
            $rowData = [];
            foreach ($headers as $header)
            {
                $rowData[] = array_key_exists($header, $item)
                    ? $item[$header]
                    : null;
            }
            $result[] = $rowData;
        }

        return $result;
    }
}