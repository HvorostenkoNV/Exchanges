<?php
declare(strict_types=1);

namespace Main\Data;

use Countable;
/** ***********************************************************************************************
 * Data interface, base project data interface
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
interface Data extends Countable
{
    /** **********************************************************************
     * clear data
     *
     * @return void
     ************************************************************************/
    public function clear() : void;
    /** **********************************************************************
     * get data count
     *
     * @return  int                         data count
     ************************************************************************/
    public function count() : int;
    /** **********************************************************************
     * check data is empty
     *
     * @return  bool                        data is empty
     ************************************************************************/
    public function isEmpty() : bool;
}