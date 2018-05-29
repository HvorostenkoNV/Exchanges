<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Application data-processors manager
 * Provides data-processors ability work with
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
class Manager
{
    /** **********************************************************************
     * get collector processor
     *
     * @param   Procedure   $procedure      procedure
     * @return  Collector                   collector processor
     ************************************************************************/
    public static function getCollector(Procedure $procedure) : Collector
    {
        return new Collector($procedure);
    }
    /** **********************************************************************
     * get matcher processor
     *
     * @param   Procedure   $procedure      procedure
     * @return  Matcher                     matcher processor
     ************************************************************************/
    public static function getMatcher(Procedure $procedure) : Matcher
    {
        return new Matcher($procedure);
    }
    /** **********************************************************************
     * get combiner processor
     *
     * @param   Procedure   $procedure      procedure
     * @return  Combiner                    combiner processor
     ************************************************************************/
    public static function getCombiner(Procedure $procedure) : Combiner
    {
        return new Combiner($procedure);
    }
    /** **********************************************************************
     * get provider processor
     *
     * @param   Procedure   $procedure      procedure
     * @return  Provider                    provider processor
     ************************************************************************/
    public static function getProvider(Procedure $procedure) : Provider
    {
        return new Provider($procedure);
    }
}