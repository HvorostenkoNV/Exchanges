<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    Main\Exchange\DataProcessors\Results\CollectedData,
    Main\Exchange\DataProcessors\Results\MatchedData;
/** ***********************************************************************************************
 * Matcher data-processor
 * match items off different participants
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Matcher extends AbstractProcessor
{
    /** **********************************************************************
     * collect procedure participants data
     *
     * @param   CollectedData $collectedData    collected data
     * @return  MatchedData                     matcher result
     ************************************************************************/
    public function matchItems(CollectedData $collectedData) : MatchedData
    {
        return new MatchedData;
    }
}