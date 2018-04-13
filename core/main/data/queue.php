<?php
declare(strict_types=1);

namespace Main\Data;

use RuntimeException;
/** ***********************************************************************************************
 * Queue interface, data type of "First In, First Out"
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
interface Queue extends Data
{
    /** **********************************************************************
     * get data form queue start
     * @return  mixed               data
     * @throws  RuntimeException    if no data for pop
     ************************************************************************/
    public function pop();
    /** **********************************************************************
     * get data form queue start
     * @param   mixed               data
     ************************************************************************/
    public function push($data) : void;
}