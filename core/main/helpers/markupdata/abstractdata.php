<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    RuntimeException,
    SplFileInfo;
/** ***********************************************************************************************
 * Abstract structure data class
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractData implements Data
{
    private $file = null;
    /** **********************************************************************
     * constructor
     *
     * @param   SplFileInfo $file           file
     ************************************************************************/
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }
    /** **********************************************************************
     * read markup data file and get data from it
     *
     * @return  array                       data
     * @throws  RuntimeException            reading error
     ************************************************************************/
    final public function read() : array
    {
        try
        {
            $fileSize       = $this->file->getSize();
            $fileContent    = $this->file->openFile('r')->fread($fileSize);

            return $this->parseData($fileContent);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * write data into markup data file
     *
     * @param   array       $data           data
     * @return  bool                        writing result
     ************************************************************************/
    final public function write(array $data) : bool
    {
        try
        {
            $dataForWriting = $this->prepareDataForWriting($data);
            $writingResult  = $this->file->openFile('w')->fwrite($dataForWriting);

            return $writingResult === 0 ? false : true;
        }
        catch (RuntimeException $exception)
        {
            return false;
        }
    }
    /** **********************************************************************
     * parse data from string
     *
     * @param   string  $content            file content for parsing
     * @return  array                       data
     ************************************************************************/
    abstract protected function parseData(string $content) : array;
    /** **********************************************************************
     * prepare data for writing into file
     *
     * @param   array       $data           data
     * @return  string                      data for writing
     ************************************************************************/
    abstract protected function prepareDataForWriting(array $data) : string;
}