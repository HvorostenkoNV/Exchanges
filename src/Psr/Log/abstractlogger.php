<?php
declare(strict_types=1);

namespace Psr\Log;
/** ***********************************************************************************************
 * This is a simple Logger implementation that other Loggers can inherit from.
 *
 * It simply delegates all log-level-specific methods to the 'log' method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 *
 * @package exchange_psr_log
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractLogger implements LoggerInterface
{
    /** **********************************************************************
     * System is unusable.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function emergency(string $message, array $context = []) : void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    /** **********************************************************************
     * Action must be taken immediately.
     *
     * Example: entire website down, database unavailable, etc, this should
     * trigger the SMS alerts and wake you up.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function alert(string $message, array $context = []) : void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    /** **********************************************************************
     * Critical conditions.
     *
     * Example: application component unavailable, unexpected exception.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function critical(string $message, array $context = []) : void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    /** **********************************************************************
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function error(string $message, array $context = []) : void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    /** **********************************************************************
     * Exceptional occurrences that are not errors.
     *
     * Example: use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function warning(string $message, array $context = []) : void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    /** **********************************************************************
     * Normal but significant events.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function notice(string $message, array $context = []) : void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    /** **********************************************************************
     * Interesting events.
     *
     * Example: user logs in, SQL logs.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function info(string $message, array $context = []) : void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    /** **********************************************************************
     * Detailed debug information.
     *
     * @param   string  $message            message text
     * @param   array   $context            context data
     * @return  void
     ************************************************************************/
    public function debug(string $message, array $context = []) : void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}