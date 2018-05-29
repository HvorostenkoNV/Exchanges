<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors\Results;

use
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\ItemData;
/** ***********************************************************************************************
 * Participants matched data
 * Describes matching between items of different participants data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class MatcherResult implements Result
{
    /** **********************************************************************
     * set participant item
     *
     * @param   int         $itemId         item ID
     * @param   Participant $participant    participant
     * @param   ItemData    $itemData       item data
     * //TODO
     ************************************************************************/
    public function setItem(int $itemId, Participant $participant, ItemData $itemData) : void
    {

    }
    /** **********************************************************************
     * get participant item
     *
     * @param   int         $itemId         item ID
     * @param   Participant $participant    participant
     * @return  ItemData|null               item data
     * //TODO
     ************************************************************************/
    public function getItem(int $itemId, Participant $participant) : ?ItemData
    {
        return new ItemData;
    }
    /** **********************************************************************
     * get items ID array
     *
     * @return  int[]                       items ID array
     * //TODO
     ************************************************************************/
    public function getItemsId() : array
    {
        return [];
    }
}