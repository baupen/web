<?php

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait FileResponseTrait
{
    private function tryCreateInlineFileResponse(?string $path, string $filename, bool $cache): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, ResponseHeaderBag::DISPOSITION_INLINE, $filename, $cache);
    }

    private function tryCreateAttachmentFileResponse(?string $path, string $filename): BinaryFileResponse
    {
        return $this->tryCreateFileResponse($path, ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename, false);
    }

    private function tryCreateFileResponse(?string $path, string $disposition, string $filename, bool $cache): BinaryFileResponse
    {
        if (null === $path) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);

        $response->setContentDisposition($disposition, $filename);

        if ($cache) {
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
            $response->setMaxAge(60 * 60 * 24 * 14); // cache for 14 days
            $response->setPrivate();
        }

        return $response;
    }
}
