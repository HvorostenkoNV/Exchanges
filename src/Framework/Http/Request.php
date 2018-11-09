<?php
declare(strict_types=1);

namespace Main\Http;

use
    InvalidArgumentException,
    Psr\Http\Message\StreamInterface,
    Psr\Http\Message\UriInterface,
    Psr\Http\Message\RequestInterface;
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
class Request extends AbstractMessage implements RequestInterface
{
    private
        $uri                = null,
        $method             = null,
        $requestTarget      = null;
    private static
        $defaultMethod      = 'GET',
        $availableMethods   =
            [
                'GET',  'POST',     'PUT',      'DELETE',
                'HEAD', 'CONNECT',  'OPTIONS',  'TRACE'
            ];
    /** **********************************************************************
     * @param   UriInterface    $uri        URI for the request.
     * @param   string          $method     HTTP request method.
     * @param   StreamInterface $body       Message body.
     * @param   array           $headers    Headers for the message.
     ************************************************************************/
    public function __construct(UriInterface $uri, string $method = '', StreamInterface $body, array $headers = [])
    {
        $requestTarget = $this->getRequestTargetFromUri($uri);

        try
        {
            $this->uri              = $uri;
            $this->method           = $this->validateMethod($method);
            $this->requestTarget    = $this->validateRequestTarget($requestTarget);
        }
        catch (InvalidArgumentException $exception)
        {

        }

        $this->setBody($body);

        foreach ($headers as $key => $values)
        {
            try
            {
                $this->setHeader($key, (array) $values);
            }
            catch (InvalidArgumentException $exception)
            {

            }
        }

        if (!$this->hasHeader('Host'))
        {
            try
            {
                $host = $this->getHostFromUri($uri);
                $this->setHeader('Host', [$host]);
            }
            catch (InvalidArgumentException $exception)
            {

            }
        }
    }
    /** **********************************************************************
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string                           Request target.
     ************************************************************************/
    public function getRequestTarget() : string
    {
        return !is_null($this->requestTarget)
            ? $this->requestTarget
            : '/';
    }
    /** **********************************************************************
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-5.3
     *
     * @param   string $requestTarget           Request target.
     * @return  RequestInterface                Instance with the specific request-target.
     ************************************************************************/
    public function withRequestTarget(string $requestTarget) : RequestInterface
    {
        $newInstance = clone $this;

        try
        {
            $newInstance->requestTarget = $this->validateRequestTarget($requestTarget);
        }
        catch (InvalidArgumentException $exception)
        {

        }

        return $newInstance;
    }
    /** **********************************************************************
     * Retrieves the HTTP method of the request.
     *
     * @return string                           Request method.
     ************************************************************************/
    public function getMethod() : string
    {
        return !is_null($this->method)
            ? $this->method
            : self::$defaultMethod;
    }
    /** **********************************************************************
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param   string $method                  Case-sensitive method.
     * @return  RequestInterface                Instance with the provided HTTP method.
     * @throws  InvalidArgumentException        Invalid HTTP methods.
     ************************************************************************/
    public function withMethod(string $method) : RequestInterface
    {
        $newInstance = clone $this;

        try
        {
            $newInstance->method = $this->validateMethod($method);
        }
        catch (InvalidArgumentException $exception)
        {

        }

        return $newInstance;
    }
    /** **********************************************************************
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @return  UriInterface                    URI of the request.
     ************************************************************************/
    public function getUri() : UriInterface
    {
        return $this->uri;
    }
    /** **********************************************************************
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param   UriInterface    $uri            New request URI to use.
     * @param   bool            $preserveHost   Preserve the original state of the Host header.
     * @return  RequestInterface                Instance with the provided URI.
     ************************************************************************/
    public function withUri(UriInterface $uri, bool $preserveHost = false) : RequestInterface
    {
        $newInstance = clone $this;

        if ($preserveHost)
        {
            try
            {
                $host = $this->getHostFromUri($uri);
                $newInstance->setHeader('Host', [$host]);
            }
            catch (InvalidArgumentException $exception)
            {

            }
        }

        return $newInstance;
    }
    /** **********************************************************************
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     * Represent the headers as a string:
     * foreach ($message->getHeaders() as $name => $values)
     * {
     *     echo $name . ': ' . implode(', ', $values);
     * }
     *
     * Emit headers iteratively:
     * foreach ($message->getHeaders() as $name => $values)
     * {
     *     foreach ($values as $value)
     *     {
     *         header(sprintf('%s: %s', $name, $value), false);
     *     }
     * }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return  string[][]                  Associative array of the message's headers.
     *                                      Each key MUST be a header name, each
     *                                      value MUST be an array of strings
     *                                      for that header.
     ************************************************************************/
    public function getHeaders() : array
    {
        $headers    = parent::getHeaders();
        $hostHeader = [];

        foreach ($headers as $key => $values)
        {
            if (strtolower($key) == 'host')
            {
                $hostHeader = [$key => $values];
                unset($headers[$key]);
            }
        }

        return array_merge($hostHeader, $headers);
    }
    /** **********************************************************************
     * Retrieve the host from the URI instance.
     *
     * @param   UriInterface $uri           URI.
     * @return  string                      Host.
     ************************************************************************/
    private function getHostFromUri(UriInterface $uri) : string
    {
        $host   = $uri->getHost();
        $port   = $uri->getPort();
        $result = $host;

        if (!is_null($port) && strlen($result) > 0)
        {
            $result .= ":$port";
        }

        return $result;
    }
    /** **********************************************************************
     * Retrieve request target from the URI instance.
     *
     * @param   UriInterface $uri           URI.
     * @return  string                      Request target.
     ************************************************************************/
    private function getRequestTargetFromUri(UriInterface $uri) : string
    {
        $path   = $uri->getPath();
        $query  = $uri->getQuery();
        $result = $path;

        if (strlen($query) > 0 && strlen($result) > 0)
        {
            $result .= "?$query";
        }

        return $result;
    }
    /** **********************************************************************
     * Validate method.
     *
     * @param   string $method              Request method.
     * @return  string                      Validated request method.
     * @throws  InvalidArgumentException    Validating error.
     ************************************************************************/
    private function validateMethod(string $method) : string
    {
        $method = strtoupper($method);

        if (!in_array($method, self::$availableMethods))
        {
            throw new InvalidArgumentException;
        }

        return $method;
    }
    /** **********************************************************************
     * Validate request target.
     *
     * @param   string $requestTarget       Request target.
     * @return  string                      Validated request target.
     * @throws  InvalidArgumentException    Validating error.
     ************************************************************************/
    private function validateRequestTarget(string $requestTarget) : string
    {
        if (strlen($requestTarget) <= 0)
        {
            throw new InvalidArgumentException;
        }
        if (preg_match('#\s#', $requestTarget))
        {
            throw new InvalidArgumentException;
        }

        return $requestTarget;
    }
}