<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    Main\Exchange\DataProcessors\Results\CollectedData,
    Main\Exchange\DataProcessors\Results\MatcherResult;
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
     * @param   CollectedData   $collectedData  collected data
     * @return  MatcherResult                   matcher result
     * //TODO
     ************************************************************************/
    public function matchItems(CollectedData $collectedData) : MatcherResult
    {
        return new MatcherResult;
    }
}