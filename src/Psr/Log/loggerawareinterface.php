<?php
declare(strict_types=1);

namespace Psr\Log;
/** ***********************************************************************************************
 * Describes a logger-aware instance.
 *
 * @package exchange_psr_log
 * @author  Hvorostenko
 *************************************************************************************************/
interface LoggerAwareInterface
{
    /** **********************************************************************
     * Sets a logger instance on the object.
     *
     * @param   LoggerInterface $logger     logger
     * @return  void
     ************************************************************************/
    public function setLogger(LoggerInterface $logger);
}