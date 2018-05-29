<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Application abstract data-processor
 *
 * @package exchange_exchange_dataprocessors
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractProcessor implements Processor
{
    private $procedure = null;
    /** **********************************************************************
     * constructor
     *
     * @param   Procedure   $procedure      procedure
     ************************************************************************/
    final public function __construct(Procedure $procedure)
    {
        $this->procedure = $procedure;
    }
    /** **********************************************************************
     * get data-processor procedure
     *
     * @return  Procedure                   procedure
     ************************************************************************/
    final public function getProcedure() : Procedure
    {
        return $this->procedure;
    }
}