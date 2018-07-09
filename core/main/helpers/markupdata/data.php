<?php
declare(strict_types=1);

namespace Main\Helpers\MarkupData;

use SplFileInfo;
/** ***********************************************************************************************
 * Abstract structure data interface
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
interface Data
{
    /** **********************************************************************
     * constructor
     *
     * @param   SplFileInfo $file           file
     ************************************************************************/
    public function __construct(SplFileInfo $file);
    /** **********************************************************************
     * read markup data file and get data from it
     *
     * @return  array                       data
     ************************************************************************/
    public function read() : array;
    /** **********************************************************************
     * write data into markup data file
     *
     * @param   array       $data           data
     * @return  bool                        writing result
     ************************************************************************/
    public function write(array $data) : bool;
}