<?php
declare(strict_types=1);

namespace Psr\Http\Server;

use
    Psr\Http\Message\ResponseInterface,
    Psr\Http\Message\ServerRequestInterface;
/** ***********************************************************************************************
 * Participant in processing a server request and response.
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the request, generating the response, or forwarding the
 * request to a subsequent middleware and possibly acting on its response.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface MiddlewareInterface
{
    /** **********************************************************************
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param   ServerRequestInterface  $request    Server request.
     * @param   RequestHandlerInterface $handler    Request handler.
     * @return  ResponseInterface                   Response.
     ************************************************************************/
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface;
}