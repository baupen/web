<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\External\Api\Share;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Enum\ApiStatus;
use App\Tests\Controller\External\Api\Base\ApiController;
use function count;
use Exception;
use function is_array;
use Symfony\Component\HttpFoundation\Response;

class FilterControllerTest extends ApiController
{
    /**
     * @var Filter|null
     */
    private $filter = null;

    /**
     * @param $relativeLink
     * @param null $payload
     *
     * @throws Exception
     *
     * @return Response
     */
    private function authenticatedRequest($relativeLink, $payload = null)
    {
        $client = $this->getExternalClient();

        if (null === $this->filter) {
            $doctrine = $client->getContainer()->get('doctrine');
            $manager = $doctrine->getManager();

            /** @var ConstructionSite $constructionSite */
            $constructionSite = $doctrine->getRepository(ConstructionSite::class)->findOneBy([]);
            $filter = new Filter();
            $filter->setConstructionSite($constructionSite);
            $filter->filterByRegistrationStatus(true);
            $filter->setPublicAccessIdentifier();

            $manager->persist($filter);
            $manager->flush();

            $this->filter = $filter;
        }

        $url = '/external/api/share/f/'.$this->filter->getPublicAccessIdentifier().$relativeLink;
        if (null === $payload) {
            $client->request('GET', $url);
        } else {
            $client->request(
                'POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'],
                $client->getContainer()->get('serializer')->serialize($payload, 'json')
            );
        }

        return $client->getResponse();
    }

    /**
     * @throws Exception
     */
    public function testMapsList()
    {
        $response = $this->authenticatedRequest('/maps/list');
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->maps);

        $this->assertTrue(is_array($mapData->data->maps));
        $onceProperties = ['reviewedAt', 'reviewedByName'];
        $once = [false, false];
        foreach ($mapData->data->maps as $map) {
            $this->assertNotNull($map);
            $this->assertObjectHasAttribute('name', $map);
            $this->assertObjectHasAttribute('context', $map);
            $this->assertObjectHasAttribute('imageShareView', $map);
            $this->assertObjectHasAttribute('imageFull', $map);

            $this->assertTrue(is_array($map->issues));
            foreach ($map->issues as $issue) {
                $this->assertObjectHasAttribute('registeredAt', $issue);
                $this->assertObjectHasAttribute('registrationByName', $issue);
                $this->assertObjectHasAttribute('description', $issue);
                $this->assertObjectHasAttribute('imageShareView', $issue);
                $this->assertObjectHasAttribute('imageFull', $issue);
                $this->assertObjectNotHasAttribute('responseLimit', $issue);
                $this->assertObjectHasAttribute('number', $issue);
                $this->assertObjectHasAttribute('id', $issue);

                for ($i = 0; $i < count($onceProperties); ++$i) {
                    $once[$i] = $once[$i] || property_exists($issue, $onceProperties[$i]);
                }
            }
        }

        foreach ($once as $item) {
            $this->assertTrue($item);
        }
    }

    /**
     * @throws Exception
     */
    public function testRead()
    {
        $response = $this->authenticatedRequest('/read');
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->constructionSite);
        $this->assertNotNull($mapData->data->filter);
        $this->assertObjectHasAttribute('name', $mapData->data->constructionSite);
        $this->assertObjectHasAttribute('reportUrl', $mapData->data->filter);
    }
}
