<?php
declare(strict_types=1);

namespace Main\Http;

use
    RuntimeException,
    Psr\Http\Message\StreamInterface;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation
 *
 * @package exchange_http
 * @author  Hvorostenko
 *************************************************************************************************/
class Stream implements StreamInterface
{
    private
        $stream             = null,
        $isResource         = false;
    private static
        $readableModeList   =
            [
                'r', 'r+',
                'w', 'w+',
                'a', 'a+',
                'x', 'x+',
                'c', 'c+'
            ],
        $writableModeList   =
            [
                'r+',
                'w', 'w+',
                'a', 'a+',
                'x', 'x+',
                'c', 'c+'
            ];
    /** **********************************************************************
     * Constructor.
     *
     * @param   resource $stream            Stream.
     ************************************************************************/
    public function __construct($stream)
    {
        $this->attach($stream);
    }
    /** **********************************************************************
     * Attach a new stream/resource to the instance.
     *
     * @param   resource $stream            Stream.
     * @return  void
     ************************************************************************/
    public function attach($stream) : void
    {
        $isResource         = is_resource($stream);
        $this->stream       = $isResource ? $stream : null;
        $this->isResource   = $isResource;
    }
    /** **********************************************************************
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return  string                      All data from the stream.
     ************************************************************************/
    public function __toString() : string
    {
        if (!$this->isReadable())
        {
            return '';
        }

        try
        {
            if ($this->isSeekable())
            {
                $this->rewind();
            }

            return $this->getContents();
        }
        catch (RuntimeException $exception)
        {
            return '';
        }
    }
    /** **********************************************************************
     * Closes the stream and any underlying resources.
     *
     * @return void
     ************************************************************************/
    public function close() : void
    {
        $stream = $this->detach();
        if (!is_null($stream))
        {
            @fclose($stream);
        }
    }
    /** **********************************************************************
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null                Underlying PHP stream, if any.
     ************************************************************************/
    public function detach()
    {
        $stream = $this->stream;

        $this->stream       = null;
        $this->isResource   = false;

        return $stream;
    }
    /** **********************************************************************
     * Get the size of the stream if known.
     *
     * @return int|null                     Size in bytes if known, or null if unknown.
     ************************************************************************/
    public function getSize() : ?int
    {
        $streamStats = $this->isResource
            ? fstat($this->stream)
            : [];

        return isset($streamStats['size']) && is_numeric($streamStats['size'])
            ? (int) $streamStats['size']
            : null;
    }
    /** **********************************************************************
     * Returns the current position of the file read/write pointer.
     *
     * @return  int                         Position of the file pointer.
     * @throws  RuntimeException            Error.
     ************************************************************************/
    public function tell() : int
    {
        if (!$this->isResource)
        {
            throw new RuntimeException('no resource available');
        }

        $cursorPosition = ftell($this->stream);
        if (!is_int($cursorPosition))
        {
            throw new RuntimeException('stream reading error');
        }

        return $cursorPosition;
    }
    /** **********************************************************************
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool                         Stream is at the end of the stream.
     ************************************************************************/
    public function eof() : bool
    {
        return $this->isResource
            ? feof($this->stream)
            : true;
    }
    /** **********************************************************************
     * Returns whether or not the stream is seekable.
     *
     * @return bool                         Stream is seekable.
     ************************************************************************/
    public function isSeekable() : bool
    {
        $streamMeta = $this->getStreamMetaData();

        return $streamMeta['seekable'];
    }
    /** **********************************************************************
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     *
     * @param   int $offset                 Stream offset.
     * @param   int $whence                 Specifies how the cursor position
     *                                      will be calculated based on the seek offset.
     *                                      Valid values are identical to the built-in
     *                                      PHP $whence values for `fseek()`.
     *                                      SEEK_SET: Set position equal to offset bytes
     *                                      SEEK_CUR: Set position to current location plus offset
     *                                      SEEK_END: Set position to end-of-stream plus offset.
     * @return  void
     * @throws  RuntimeException            Failure.
     ************************************************************************/
    public function seek(int $offset, int $whence = SEEK_SET) : void
    {
        if (!$this->isResource)
        {
            throw new RuntimeException('no resource available');
        }
        if (!$this->isSeekable())
        {
            throw new RuntimeException('stream is not seekable');
        }

        $seekResult = fseek($this->stream, $offset, $whence);
        if ($seekResult !== 0)
        {
            throw new RuntimeException('stream reading error');
        }
    }
    /** **********************************************************************
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @see http://www.php.net/manual/en/function.fseek.php
     *
     * @return  void
     * @throws  RuntimeException            Failure.
     ************************************************************************/
    public function rewind() : void
    {
        try
        {
            $this->seek(0);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * Returns whether or not the stream is writable.
     *
     * @return bool                         Stream is writable.
     ************************************************************************/
    public function isWritable() : bool
    {
        $streamMeta = $this->getStreamMetaData();

        return in_array($streamMeta['mode'], self::$writableModeList);
    }
    /** **********************************************************************
     * Write data to the stream.
     *
     * @param   string $string              String that is to be written.
     * @return  int                         Number of bytes written to the stream.
     * @throws  RuntimeException            Failure.
     ************************************************************************/
    public function write(string $string) : int
    {
        if (!$this->isResource)
        {
            throw new RuntimeException('no resource available');
        }
        if (!$this->isWritable())
        {
            throw new RuntimeException('stream is not writable');
        }

        $writeResult = @fwrite($this->stream, $string);
        if (!is_int($writeResult))
        {
            throw new RuntimeException('stream writing error');
        }

        return $writeResult;
    }
    /** **********************************************************************
     * Returns whether or not the stream is readable.
     *
     * @return bool                         Stream is readable.
     ************************************************************************/
    public function isReadable() : bool
    {
        $streamMeta = $this->getStreamMetaData();

        return in_array($streamMeta['mode'], self::$readableModeList);
    }
    /** **********************************************************************
     * Read data from the stream.
     *
     * @param   int $length                 Read up to $length bytes from the object
     *                                      and return them. Fewer than $length bytes
     *                                      may be returned if underlying stream
     *                                      call returns fewer bytes.
     * @return  string                      Data read from the stream,
     *                                      or an empty string if no bytes are available.
     * @throws  RuntimeException            Error occurs.
     ************************************************************************/
    public function read(int $length) : string
    {
        if (!$this->isResource)
        {
            throw new RuntimeException('no resource available');
        }
        if (!$this->isReadable())
        {
            throw new RuntimeException('stream is not readable');
        }

        $readResult = @fread($this->stream, $length);
        if (!is_string($readResult))
        {
            throw new RuntimeException('stream reading error');
        }

        return $readResult;
    }
    /** **********************************************************************
     * Returns the remaining contents in a string
     *
     * @return  string                      Remaining contents.
     * @throws  RuntimeException            Unable to read or occurs while reading.
     ************************************************************************/
    public function getContents() : string
    {
        if (!$this->isResource)
        {
            throw new RuntimeException('no resource available');
        }
        if (!$this->isReadable())
        {
            throw new RuntimeException('stream is not readable');
        }

        $readResult = stream_get_contents($this->stream);
        if (!is_string($readResult))
        {
            throw new RuntimeException('stream reading error');
        }

        return $readResult;
    }
    /** **********************************************************************
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param   string $key                 Specific metadata to retrieve.
     * @return  array|mixed|null            Returns an associative array
     *                                      if no key is provided. Returns a specific
     *                                      key value if a key is provided and the
     *                                      value is found, or null if the key is not found.
     ************************************************************************/
    public function getMetadata(string $key = '')
    {
        $streamMeta = $this->getStreamMetaData();

        return $streamMeta[$key] ?? null;
    }
    /** **********************************************************************
     * Get stream meta data.
     *
     * @return  array                       Stream meta data.
     ************************************************************************/
    private function getStreamMetaData() : array
    {
        $streamMeta = $this->isResource
            ? stream_get_meta_data($this->stream)
            : [];

        $streamMeta['timed_out']    =
            isset($streamMeta['timed_out']) && is_bool($streamMeta['timed_out'])
                ? $streamMeta['timed_out']
                : false;
        $streamMeta['blocked']      =
            isset($streamMeta['blocked']) && is_bool($streamMeta['blocked'])
                ? $streamMeta['blocked']
                : false;
        $streamMeta['stream_type']  =
            isset($streamMeta['stream_type']) && is_string($streamMeta['stream_type'])
                ? $streamMeta['stream_type']
                : '';
        $streamMeta['wrapper_type'] =
            isset($streamMeta['wrapper_type']) && is_string($streamMeta['wrapper_type'])
                ? $streamMeta['wrapper_type']
                : '';
        $streamMeta['mode']         =
            isset($streamMeta['mode']) && is_string($streamMeta['mode'])
                ? $streamMeta['mode']
                : '';
        $streamMeta['seekable']     =
            isset($streamMeta['seekable']) && is_bool($streamMeta['seekable'])
                ? $streamMeta['seekable']
                : false;
        $streamMeta['uri']          =
            isset($streamMeta['uri']) && is_string($streamMeta['uri'])
                ? $streamMeta['uri']
                : '';

        return $streamMeta;
    }
}