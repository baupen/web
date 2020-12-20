<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use App\DataFixtures\Model\AssetFile;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as StatusCode;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait AssertFileTrait
{
    private function assertPostFile(KernelBrowser $client, string $url, AssetFile $file)
    {
        $client->request('POST', $url, [], ['file' => $file]);

        $this->assertResponseStatusCodeSame(StatusCode::HTTP_CREATED);

        return $client->getResponse()->getContent();
    }

    private function assertGetFile(KernelBrowser $client, string $url, string $mode = ResponseHeaderBag::DISPOSITION_INLINE)
    {
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $this->assertStringStartsWith($mode, $client->getResponse()->headers->get('content-disposition'));
    }

    private function assertFileNotFound(KernelBrowser $client, string $url)
    {
        $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
