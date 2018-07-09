<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * Unknown procedure field exception
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class UnknownProcedureFieldException extends Exception implements Throwable
{
    private $procedureCode = '';
    /** **********************************************************************
     * set procedure code
     *
     * @param   string $procedureCode       procedure code
     ************************************************************************/
    public function setProcedureCode(string $procedureCode) : void
    {
        $this->procedureCode = $procedureCode;
    }
    /** **********************************************************************
     * get procedure code
     *
     * @return  string                      procedure code
     ************************************************************************/
    public function getProcedureCode() : string
    {
        return $this->procedureCode;
    }
}