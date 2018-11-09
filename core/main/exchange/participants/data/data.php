<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\Queue;
/** ***********************************************************************************************
 * Participants data interface, base participants data interface
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
interface Data extends Queue
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  ItemData                    data
     * @throws  RuntimeException            if no data for extract
     ************************************************************************/
    public function pop();
    /** **********************************************************************
     * push data to the end
     *
     * @param   ItemData $data              data
     * @return  void
     * @throws  InvalidArgumentException    incorrect pushed data
     ************************************************************************/
    public function push($data) : void;
}