<?php
declare(strict_types=1);

namespace Main\Http;

use
    RuntimeException,
    InvalidArgumentException,
    SplFileInfo,
    Psr\Http\Message\StreamInterface,
    Psr\Http\Message\StreamFactoryInterface;
/** ***********************************************************************************************
 * Streams factory class. Constructs streams based on the input type.
 *
 * @package exchange_http
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamFactory implements StreamFactoryInterface
{
    private static $availableModeList =
        [
            'r', 'r+',
            'w', 'w+',
            'a', 'a+',
            'x', 'x+',
            'c', 'c+',
            'e'
        ];
    /** **********************************************************************
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param   string $content             String content with which to populate the stream.
     * @return  StreamInterface             New stream.
     ************************************************************************/
    public function createStream(string $content = '') : StreamInterface
    {
        $resource = @fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }
    /** **********************************************************************
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param   string  $filename           The filename or stream URI to use
     *                                      as basis of stream.
     * @param   string  $mode               The mode with which to open
     *                                      the underlying filename/stream.
     * @return  StreamInterface             Stream.
     * @throws  RuntimeException            File cannot be opened.
     * @throws  InvalidArgumentException    Mode is invalid.
     ************************************************************************/
    public function createStreamFromFile(string $filename, string $mode = 'r') : StreamInterface
    {
        $file = new SplFileInfo($filename);

        if (!$file->isFile())
        {
            throw new RuntimeException("file \"$filename\" not found");
        }
        if (!$file->isReadable())
        {
            throw new RuntimeException("file \"$filename\" is not readable");
        }
        if (!in_array($mode, self::$availableModeList))
        {
            throw new InvalidArgumentException("mode \"$mode\" is invalid");
        }

        $resource = @fopen($file->getPathname(), $mode);

        return $this->createStreamFromResource($resource);
    }
    /** **********************************************************************
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param   resource $resource          The PHP resource to use as
     *                                      the basis for the stream.
     * @return  StreamInterface             Stream.
     ************************************************************************/
    public function createStreamFromResource($resource) : StreamInterface
    {
        return new Stream($resource);
    }
}