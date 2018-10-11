<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    SplFileInfo,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException;
/** ***********************************************************************************************
 * Abstract structure data interface
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
interface Data
{
    /** **********************************************************************
     * read from file
     *
     * @param   SplFileInfo $file           file
     * @return  array                       data
     * @throws  ParseDataException          parse data error
     ************************************************************************/
    public function readFromFile(SplFileInfo $file) : array;
    /** **********************************************************************
     * read from string
     *
     * @param   string $content             content
     * @return  array                       data
     * @throws  ParseDataException          parse data error
     ************************************************************************/
    public function readFromString(string $content) : array;
    /** **********************************************************************
     * write to file
     *
     * @param   SplFileInfo $file           file
     * @param   array       $data           data
     * @throws  WriteDataException          write data error
     ************************************************************************/
    public function writeToFile(SplFileInfo $file, array $data) : void;
}