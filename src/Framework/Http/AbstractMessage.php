<?php
declare(strict_types=1);

namespace Main\Http;

use
    InvalidArgumentException,
    Psr\Http\Message\MessageInterface,
    Psr\Http\Message\StreamInterface;
/** ***********************************************************************************************
 * PSR-7 MessageInterface implementation
 *
 * @package exchange_http
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractMessage implements MessageInterface
{
    private
        $headers            = [],
        $headerNames        = [],
        $protocol           = '',
        $body               = null;
    private static
        $defaultProtocol    = '1.1';
    /** **********************************************************************
     * Retrieves the HTTP protocol version as a string.
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return  string                      HTTP protocol version.
     ************************************************************************/
    public function getProtocolVersion() : string
    {
        return strlen($this->protocol) > 0
            ? $this->protocol
            : static::$defaultProtocol;
    }
    /** **********************************************************************
     * Return an instance with the specified HTTP protocol version.
     * The version string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the immutability
     * of the message, and MUST return an instance that has the new protocol version.
     *
     * @param   string $version             HTTP protocol version.
     * @return  MessageInterface            Instance with the specified HTTP protocol version.
     ************************************************************************/
    public function withProtocolVersion(string $version) : MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->setProtocolVersion($version);

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
        return $this->headers;
    }
    /** **********************************************************************
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param   string $name                Case-insensitive header field name.
     * @return  bool                        Any header names match the given header
     *                                      name using a case-insensitive string
     *                                      comparison. Returns false if no matching
     *                                      header name is found in the message.
     ************************************************************************/
    public function hasHeader(string $name) : bool
    {
        $headerNameLowercase = strtolower($name);

        return isset($this->headerNames[$headerNameLowercase]);
    }
    /** **********************************************************************
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty array.
     *
     * @param   string $name                Case-insensitive header field name.
     * @return  string[]                    Array of string values as provided
     *                                      for the given header. If the header
     *                                      does not appear in the message,
     *                                      this method MUST return an empty array.
     ************************************************************************/
    public function getHeader(string $name) : array
    {
        $headerNameLowercase    = strtolower($name);
        $headerName             = $this->headerNames[$headerNameLowercase] ?? null;

        return $this->headers[$headerName] ?? [];
    }
    /** **********************************************************************
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given case-insensitive
     * header name as a string concatenated together using a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param   string $name                Case-insensitive header field name.
     * @return  string                      String of values as provided for
     *                                      the given header concatenated together
     *                                      using a comma. If the header does not
     *                                      appear in the message, this method MUST
     *                                      return an empty string.
     ************************************************************************/
    public function getHeaderLine(string $name) : string
    {
        $values = $this->getHeader($name);

        return implode(', ', $values);
    }
    /** **********************************************************************
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param   string          $name       Case-insensitive header field name.
     * @param   string|string[] $value      Header value(s).
     * @return  MessageInterface            Instance with the provided value
     *                                      replacing the specified header.
     * @throws  InvalidArgumentException    Invalid header names or values.
     ************************************************************************/
    public function withHeader(string $name, $value) : MessageInterface
    {
        try
        {
            $newInstance = clone $this;
            $newInstance->setHeader($name, (array) $value);

            return $newInstance;
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param   string          $name       Case-insensitive header field name to add.
     * @param   string|string[] $value      Header value(s).
     * @return  MessageInterface            Instance with the specified header
     *                                      appended with the given value.
     * @throws  InvalidArgumentException    Invalid header names or values.
     ************************************************************************/
    public function withAddedHeader(string $name, $value) : MessageInterface
    {
        try
        {
            $headerOldValues    = $this->getHeader($name);
            $headerNewValues    = (array) $value;
            $headerFullValues   = array_merge($headerOldValues, $headerNewValues);
            $newInstance        = clone $this;

            $newInstance->setHeader($name, $headerFullValues);
            return $newInstance;
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param   string $name                Case-insensitive header field name to remove.
     * @return  MessageInterface            Instance without the specified header.
     ************************************************************************/
    public function withoutHeader(string $name) : MessageInterface
    {
        $headerNameLowercase    = strtolower($name);
        $headerName             = $this->headerNames[$headerNameLowercase] ?? null;
        $newInstance            = clone $this;

        unset
        (
            $newInstance->headerNames[$headerNameLowercase],
            $newInstance->headers[$headerName]
        );

        return $newInstance;
    }
    /** **********************************************************************
     * Gets the body of the message.
     *
     * @return  StreamInterface             Body as a stream.
     ************************************************************************/
    public function getBody() : StreamInterface
    {
        if (is_null($this->body))
        {
            $this->body = (new StreamFactory)->createStream('');
        }

        return $this->body;
    }
    /** **********************************************************************
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param   StreamInterface $body       Body.
     * @return  MessageInterface            Instance with the specified message body.
     * @throws  InvalidArgumentException    Body is not valid.
     ************************************************************************/
    public function withBody(StreamInterface $body) : MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->body = $body;

        return $newInstance;
    }
    /** **********************************************************************
     * Set protocol version.
     *
     * @param   string $protocol            HTTP protocol version.
     * @return  void
     ************************************************************************/
    protected function setProtocolVersion(string $protocol) : void
    {
        try
        {
            $this->protocol = $this->validateProtocolVersion($protocol);
        }
        catch (InvalidArgumentException $exception)
        {
            $this->protocol = '';
        }
    }
    /** **********************************************************************
     * Set headers.
     *
     * @param   string      $name           Case-insensitive header field name.
     * @param   string[]    $values         Header value(s).
     * @return  void
     * @throws  InvalidArgumentException    Invalid header names or values.
     ************************************************************************/
    protected function setHeader(string $name, array $values) : void
    {
        $headerName             = null;
        $headerNameLowercase    = null;
        $headerNameOld          = null;
        $headerValues           = [];

        try
        {
            $headerName             = $this->validateHeaderName($name);
            $headerNameLowercase    = strtolower($headerName);
            $headerNameOld          = $this->headerNames[$headerNameLowercase] ?? null;
        }
        catch (InvalidArgumentException $exception)
        {
            throw new InvalidArgumentException("header name \"$name\" is invalid");
        }

        foreach ($values as $value)
        {
            try
            {
                $headerValues[] = $this->validateHeaderValue($value);
            }
            catch (InvalidArgumentException $exception)
            {
                throw new InvalidArgumentException("header value \"$value\" is invalid");
            }
        }

        unset($this->headers[$headerNameOld]);
        $this->headerNames[$headerNameLowercase]    = $headerName;
        $this->headers[$headerName]                 = $headerValues;
    }
    /** **********************************************************************
     * Set body.
     *
     * @param   StreamInterface $body       Body.
     * @return  void
     ************************************************************************/
    protected function setBody(StreamInterface $body) : void
    {
        $this->body = $body;
    }
    /** **********************************************************************
     * Validate the HTTP protocol version.
     *
     * @param   string $version             HTTP protocol version.
     * @return  string                      Validated HTTP protocol version.
     * @throws  InvalidArgumentException    Validating error.
     ************************************************************************/
    private function validateProtocolVersion(string $version) : string
    {
        if (strlen($version) <= 0)
        {
            throw new InvalidArgumentException;
        }
        if (!preg_match('#^(1\.[01]|2)$#', $version))
        {
            throw new InvalidArgumentException;
        }

        return $version;
    }
    /** **********************************************************************
     * Validate header name.
     *
     * @param   string $name                Header name.
     * @return  string                      Validated header name.
     * @throws  InvalidArgumentException    Validating error.
     ************************************************************************/
    private function validateHeaderName(string $name) : string
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name))
        {
            throw new InvalidArgumentException;
        }

        return $name;
    }
    /** **********************************************************************
     * Validate header value.
     *
     * @param   mixed $value                Header value.
     * @return  string                      Validated header value.
     * @throws  InvalidArgumentException    Validating error.
     ************************************************************************/
    private function validateHeaderValue($value) : string
    {
        if (!is_string($value) && ! is_numeric($value))
        {
            throw new InvalidArgumentException;
        }
        if ($value === '')
        {
            throw new InvalidArgumentException;
        }
        if (!preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value))
        {
            throw new InvalidArgumentException;
        }
        if (!preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value))
        {
            throw new InvalidArgumentException;
        }

        return (string) $value;
    }
}