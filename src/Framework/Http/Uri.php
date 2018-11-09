<?php
declare(strict_types=1);

namespace Main\Http;

use Psr\Http\Message\UriInterface;
/** ***********************************************************************************************
 * HTTP Request encapsulation
 *
 * Requests are considered immutable; all methods that might change state are
 * implemented such that they retain the internal state of the current
 * message and return a new instance that contains the changed state.
 *
 * @package exchange_http
 * @author  Hvorostenko
 *************************************************************************************************/
class Uri implements UriInterface
{

}