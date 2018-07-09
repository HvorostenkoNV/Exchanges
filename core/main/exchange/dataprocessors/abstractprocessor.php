<?php
declare(strict_types=1);

namespace Main\Exchange\DataProcessors;

use
    ReflectionException,
    ReflectionClass,
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
    public function __construct(Procedure $procedure)
    {
        $processorName  = null;
        $procedureCode  = $procedure->getCode();

        try
        {
            $reflection     = new ReflectionClass(static::class);
            $processorName  = $reflection->getShortName();
        }
        catch (ReflectionException $exception)
        {
            $processorName = static::class;
        }

        $this->procedure = $procedure;
        Logger::getInstance()->addNotice("$processorName for procedure \"$procedureCode\": created");
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