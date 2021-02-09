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
    private function tryCreateInlineFileResponse(?string $path, string $filename): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function tryCreateAttachmentFileResponse(?string $path, string $filename): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, $filename, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function tryCreateFileResponse(?string $path, string $filename, string $disposition)
    {
        if (null === $path) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);

        $response->setContentDisposition(
            $disposition,
            $filename
        );

        return $response;
    }
}
