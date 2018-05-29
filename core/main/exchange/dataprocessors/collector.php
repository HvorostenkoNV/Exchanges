<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use Main\Exchange\DataProcessors\Results\CollectedData;
/** ***********************************************************************************************
 * Collector data-processor
 * collects procedure participants provided data
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Collector extends AbstractProcessor
{
    /** **********************************************************************
     * collect procedure participants data
     *
     * @return  CollectedData               collected data
     * //TODO
     ************************************************************************/
    public function collectData() : CollectedData
    {
        return new CollectedData;
    }
}