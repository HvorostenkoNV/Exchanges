<?php
declare(strict_types=1);

namespace Main\Helpers\Database\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * database connection exception
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class ConnectionException extends Exception implements Throwable
{

}