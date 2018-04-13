<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData;
/** ***********************************************************************************************
 * Participants provided data. Data from participant. Data type of "First In, First Out". Collection of DBFieldsValues objects
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class ProvidedData extends QueueData implements Data
{
    /** **********************************************************************
     * get data form queue start
     * @return  ItemData                    data
     * @throws  RuntimeException            if no data for pop
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * get data form queue start
     * @param   ItemData    $data           data
     * @throws  InvalidArgumentException    expect ItemData data
     ************************************************************************/
    public function push($data) : void
    {
        if (!$data instanceof ItemData || $data->count() <= 0)
            throw new InvalidArgumentException('Pushed data required to be not empty '.ItemData::class.' object');

        parent::push($data);
    }
}