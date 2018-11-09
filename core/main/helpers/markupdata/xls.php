<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use
    Exception,
    SplFileInfo,
    PHPExcel_IOFactory,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException;
/** ***********************************************************************************************
 * XLS structure data class
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class XLS implements Data
{
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        require_once
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            CLASSES_FOLDER.DIRECTORY_SEPARATOR.
            'phpexcel'.DIRECTORY_SEPARATOR.
            'PHPExcel.php';
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
        $result = [];

        try
        {
            $excel  = PHPExcel_IOFactory::load($file->getPathname());
            $sheets = $excel->getAllSheets();

            foreach ($sheets as $sheet)
            {
                $sheetTitle     = $sheet->getTitle();
                $rowsIndexes    = array_keys($sheet->getRowDimensions());
                $columnsIndexes = array_keys($sheet->getColumnDimensions());
                $tableHeaders   = [];

                if (count($rowsIndexes) > 0)
                {
                    $headerRowIndex = array_shift($rowsIndexes);
                    foreach ($columnsIndexes as $columnIndex)
                    {
                        $cell       = $sheet->getCell($columnIndex.$headerRowIndex);
                        $cellValue  = trim($cell->getFormattedValue());

                        if (strlen($cellValue) > 0)
                        {
                            $tableHeaders[$columnIndex] = $cellValue;
                        }
                    }
                }

                $result[$sheetTitle] = [];
                foreach ($rowsIndexes as $rowIndex)
                {
                    $result[$sheetTitle][$rowIndex] = [];
                    foreach ($tableHeaders as $columnIndex => $columnTitle)
                    {
                        $cell   = $sheet->getCell($columnIndex.$rowIndex);
                        $value  = $cell->getFormattedValue();

                        $result[$sheetTitle][$rowIndex][$columnTitle] = $value;
                    }
                }
                $result[$sheetTitle] = array_values($result[$sheetTitle]);
            }
        }
        catch (Exception $exception)
        {

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
        // TODO
        throw new ParseDataException('NOT IMPLEMENTED YET');
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
        // TODO
        throw new WriteDataException('NOT IMPLEMENTED YET');
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
        // TODO
        throw new WriteDataException('NOT IMPLEMENTED YET');
    }
}