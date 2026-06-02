<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait AssertFileTrait
{
    private function assertGetFile(KernelBrowser $client, string $url, string $mode = ResponseHeaderBag::DISPOSITION_INLINE): void
    {
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $url);
        $this->assertResponseIsSuccessful();

        $this->assertStringStartsWith($mode, $client->getResponse()->headers->get('content-disposition'));
    }

    private function assertFileNotFound(KernelBrowser $client, string $url): void
    {
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
