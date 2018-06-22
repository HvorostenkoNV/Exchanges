<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    Main\Helpers\Logger,
    Main\Exchange\Procedures\Procedure;
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
        $processorName  = static::class;
        $procedureName  = get_class($procedure);

        $this->procedure = $procedure;
        Logger::getInstance()->addNotice("Processor \"$processorName\" for procedure \"$procedureName\" created");
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