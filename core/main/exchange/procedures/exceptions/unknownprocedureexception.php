<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * Unknown procedure exception
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class UnknownProcedureException extends Exception implements Throwable
{
    private $procedureCode = '';
    /** **********************************************************************
     * set procedure code
     *
     * @param   string $procedureCode       procedure code
     * @return  void
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