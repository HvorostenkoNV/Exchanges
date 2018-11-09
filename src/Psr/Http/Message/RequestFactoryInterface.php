<?php
declare(strict_types=1);

namespace Psr\Http\Message;
/** ***********************************************************************************************
 * Request factory interface.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface RequestFactoryInterface
{
    /** **********************************************************************
     * Create a new request.
     *
     * @param   string              $method     The HTTP method associated with the request.
     * @param   UriInterface|string $uri        The URI associated with the request.
     * @return  RequestInterface                Request.
     ************************************************************************/
    public function createRequest(string $method, $uri) : RequestInterface;
}