<?php
declare(strict_types=1);

namespace Psr\Http\Message;
/** ***********************************************************************************************
 * Server request factory interface.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface ServerRequestFactoryInterface
{
    /** **********************************************************************
     * Create a new server request.
     *
     * Note that server parameters are taken precisely as given - no parsing/processing
     * of the given values is performed. In particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param   string              $method         The HTTP method associated
     *                                              with the request.
     * @param   UriInterface|string $uri            The URI associated with the request.
     * @param   array               $serverParams   An array of Server API (SAPI)
     *                                              parameters with which to seed
     *                                              the generated request instance.
     * @return  ServerRequestInterface              New server request.
     ************************************************************************/
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface;
}