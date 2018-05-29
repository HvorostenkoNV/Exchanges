<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData;
/** ***********************************************************************************************
 * Participants delivered data. Data ready for delivery.
 * Queue data, collection type of "First In, First Out"
 * Collection of ItemData objects
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class DataForDelivery extends QueueData implements Data
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  ItemData                    data
     * @throws  RuntimeException            if no data for extract
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * push data to the end
     *
     * @param   ItemData    $data           data
     * @throws  InvalidArgumentException    incorrect pushed data
     ************************************************************************/
    public function push($data) : void
    {
        if (!$data instanceof ItemData || $data->count() <= 0)
        {
            $needClassName = ItemData::class;
            throw new InvalidArgumentException("pushed data required to be not empty $needClassName object");
        }

        parent::push($data);
    }
}