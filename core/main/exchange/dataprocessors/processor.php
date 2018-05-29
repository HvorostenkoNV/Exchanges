<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Application data-processor interface
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
interface Processor
{
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure   $procedure      procedure
     ************************************************************************/
    public function __construct(Procedure $procedure);
    /** **********************************************************************
     * get data-processor procedure
     *
     * @return  Procedure                   procedure
     ************************************************************************/
    public function getProcedure() : Procedure;
}