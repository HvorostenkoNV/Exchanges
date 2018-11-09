<?php
declare(strict_types=1);

namespace Psr\Http\Message;

use InvalidArgumentException;
/** ***********************************************************************************************
 * URI factory interface.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface UriFactoryInterface
{
    /** **********************************************************************
     * Create a new URI.
     *
     * @param   string $uri                 The URI to parse.
     * @return  UriInterface                URI.
     * @throws  InvalidArgumentException    Given URI cannot be parsed.
     ************************************************************************/
    public function createUri(string $uri = '') : UriInterface;
}