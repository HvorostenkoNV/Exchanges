<?php
declare(strict_types=1);

namespace Main\Data;

use RuntimeException;
/** ***********************************************************************************************
 * Queue interface, collection type of "First In, First Out"
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
interface Queue extends Data
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  mixed                       data
     * @throws  RuntimeException            no data for extract
     ************************************************************************/
    public function pop();
    /** **********************************************************************
     * push data to the end
     *
     * @param   mixed                       data
     * @return  void
     ************************************************************************/
    public function push($data) : void;
}