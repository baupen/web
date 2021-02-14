<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait FileResponseTrait
{
    private function tryCreateInlineFileResponse(?string $path, string $filename, bool $deleteFileAfterSend = false): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, ResponseHeaderBag::DISPOSITION_INLINE, $filename, $deleteFileAfterSend);
    }

    private function tryCreateAttachmentFileResponse(?string $path, string $filename, bool $deleteFileAfterSend = false): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename, $deleteFileAfterSend);
    }

    private function tryCreateFileResponse(?string $path, string $disposition, string $filename, bool $deleteFileAfterSend)
    {
        if (null === $path) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);

        $response->setContentDisposition($disposition, $filename);
        $response->deleteFileAfterSend($deleteFileAfterSend);

        return $response;
    }
}
