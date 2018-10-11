<?php
declare(strict_types=1);

namespace Main\Helpers\Database\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * database query exception
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class QueryException extends Exception implements Throwable
{

}