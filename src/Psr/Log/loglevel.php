<?php
declare(strict_types=1);

namespace Psr\Log;
/** ***********************************************************************************************
 * Describes log levels.
 *
 * @package exchange_psr_log
 * @author  Hvorostenko
 *************************************************************************************************/
class LogLevel
{
    const
        EMERGENCY = 'emergency',
        ALERT     = 'alert',
        CRITICAL  = 'critical',
        ERROR     = 'error',
        WARNING   = 'warning',
        NOTICE    = 'notice',
        INFO      = 'info',
        DEBUG     = 'debug';
}