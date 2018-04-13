<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\QueueData;
/** ***********************************************************************************************
 * Queue of matching rules of two participants
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchingRulesQueue extends QueueData
{
    /** **********************************************************************
     * get data form queue start
     * @return  string[]                    data
     * @throws  RuntimeException    if no data for pop
     ************************************************************************/
    public function pop()
    {
        return parent::pop();
    }
    /** **********************************************************************
     * get data form queue start
     * @param   string[]    $data           data
     * @throws  InvalidArgumentException    pushed data is not array of string
     ************************************************************************/
    public function push($data) : void
    {
        if (!is_array($data) || count($data) <= 0)
            throw new InvalidArgumentException('Pushed data required to be array of strings');

        foreach ($data as $value)
            if (!is_string($value))
                throw new InvalidArgumentException('Pushed data required to be array of strings');

        parent::push($data);
    }
}