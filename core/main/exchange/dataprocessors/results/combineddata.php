<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Results;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData,
    Main\Exchange\DataProcessors\Data\CombinedItem;
/** ***********************************************************************************************
 * Combined participants data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class CombinedData extends QueueData implements Result
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  CombinedItem                data
     * @throws  RuntimeException            if no data for extract
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * push data to the end
     *
     * @param   CombinedItem $data          data
     * @throws  InvalidArgumentException    incorrect pushed data
     ************************************************************************/
    public function push($data) : void
    {
        if (!$data instanceof CombinedItem)
        {
            $needClass = CombinedItem::class;
            throw new InvalidArgumentException("pushed data must be instance of \"$needClass\"");
        }

        parent::push($data);
    }
}