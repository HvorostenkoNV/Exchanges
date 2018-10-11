<?php
declare(strict_types=1);

namespace Main\Helpers\Database\Exceptions;

use
    Throwable,
    Exception;
/** ***********************************************************************************************
 * database invalid column value exception
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class InvalidArgumentException extends Exception implements Throwable
{

}