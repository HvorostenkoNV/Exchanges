<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData;
/** ***********************************************************************************************
 * DB query data, collection type of "First In, First Out"
 * Collection of DBRow objects
 * Based on db query
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class DBQueryResult extends QueueData
{
    /** **********************************************************************
     * extract queue data from the start
     *
     * @return  DBRow                       data
     * @throws  RuntimeException            if no data for extract
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * push data to the end
     *
     * @param   DBRow   $data               data
     * @throws  InvalidArgumentException    incorrect pushed data
     ************************************************************************/
    public function push($data) : void
    {
        if (!$data instanceof DBRow || $data->count() <= 0)
        {
            $needClassName = DBRow::class;
            throw new InvalidArgumentException("pushed data required to be not empty $needClassName object");
        }

        parent::push($data);
    }
}