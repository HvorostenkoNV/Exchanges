<?php
declare(strict_types=1);

namespace Psr\Http\Message;

use InvalidArgumentException;
/** ***********************************************************************************************
 * Uploaded file factory interface.
 *
 * @package exchange_psr_http
 * @author  Hvorostenko
 *************************************************************************************************/
interface UploadedFileFactoryInterface
{
    /** **********************************************************************
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the stream.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param   StreamInterface $stream             The underlying stream representing
     *                                              the uploaded file content.
     * @param   int             $size               The size of the file in bytes.
     * @param   int             $error              The PHP file upload error.
     * @param   string          $clientFilename     The filename as provided by
     *                                              the client, if any.
     * @param   string          $clientMediaType    The media type as provided by
     *                                              the client, if any.
     * @return  UploadedFileInterface               Uploaded file.
     * @throws  InvalidArgumentException            File resource is not readable.
     ************************************************************************/
    public function createUploadedFile
    (
        StreamInterface $stream,
        int             $size               = null,
        int             $error              = UPLOAD_ERR_OK,
        string          $clientFilename     = null,
        string          $clientMediaType    = null
    ) : UploadedFileInterface;
}