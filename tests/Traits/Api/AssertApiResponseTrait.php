<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait AssertApiResponseTrait
{
    private function assertApiResponseFieldSubset(ResponseInterface $response, string ...$expectedFields)
    {
        $content = $response->getContent();
        $hydraPayload = json_decode($content, true);

        $whitelist = array_merge(['@id', '@type'], $expectedFields);
        sort($whitelist);

        if ('hydra:Collection' === $hydraPayload['@type']) {
            foreach ($hydraPayload['hydra:member'] as $member) {
                $actualFields = array_keys($member);
                sort($actualFields);

                $validEntries = array_intersect($actualFields, $whitelist);
                $this->assertSameSize($validEntries, $actualFields);
            }
        } else {
            $whitelist = array_merge(['@context'], $whitelist);
            sort($whitelist);

            $actualFields = array_keys($hydraPayload);
            sort($actualFields);

            $validEntries = array_intersect($actualFields, $whitelist);
            $this->assertSameSize($validEntries, $actualFields);
        }
    }

    private function assertApiResponseFileIsDownloadable(Client $client, ResponseInterface $response, string $fileUrlProperty, string $mode = ResponseHeaderBag::DISPOSITION_INLINE, string $suffix = ''): ?string
    {
        $content = $response->getContent();
        $hydraPayload = json_decode($content, true);

        if ('hydra:Collection' === $hydraPayload['@type']) {
            foreach ($hydraPayload['hydra:member'] as $member) {
                $url = $member[$fileUrlProperty] ?? null;
                if (!$url) {
                    continue;
                }

                $url .= $suffix;

                $response = $this->assertApiGetOk($client, $url);
                $this->assertStringStartsWith($mode, $response->getHeaders()['content-disposition'][0]);

                return $url;
            }

            $this->fail('no member has a the property '.$fileUrlProperty.' set, hence can not assert this url is valid.');
        }

        $this->fail('only collections support this assertion.');
    }
}
