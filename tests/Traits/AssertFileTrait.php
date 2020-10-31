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

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait AssertFileTrait
{
    private function assertFileIsDownloadable(KernelBrowser $client, string $url, string $mode = ResponseHeaderBag::DISPOSITION_INLINE)
    {
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $this->assertStringStartsWith($mode, $client->getResponse()->headers->get('content-disposition'));
    }
}
