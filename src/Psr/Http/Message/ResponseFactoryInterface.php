<?php
declare(strict_types=1);

namespace Psr\Http\Message;
/** ***********************************************************************************************
 * Response factory interface.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface ResponseFactoryInterface
{
    /** **********************************************************************
     * Create a new response.
     *
     * @param   int     $code               The HTTP status code. Defaults to 200.
     * @param   string  $reasonPhrase       The reason phrase to associate with
     *                                      the status code in the generated response.
     *                                      If none is provided, implementations MAY use
     *                                      the defaults as suggested in the HTTP specification.
     * @return  ResponseInterface           Response.
     ************************************************************************/
    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface;
}