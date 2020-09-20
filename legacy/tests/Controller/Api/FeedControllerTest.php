<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;

class FeedControllerTest extends ApiController
{
    public function testFeedList()
    {
        $url = '/api/feed/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $data = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($data->data);
        $this->assertNotNull($data->data->feed);
        $this->assertNotEmpty($data->data->feed->entries);
        foreach ($data->data->feed->entries as $entry) {
            $this->assertObjectHasAttribute('id', $entry);
            $this->assertObjectHasAttribute('craftsman', $entry);
            $this->assertObjectHasAttribute('timestamp', $entry);
            $this->assertObjectHasAttribute('type', $entry);
        }
    }
}
