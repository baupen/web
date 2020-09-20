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

class StatisticsControllerTest extends ApiController
{
    public function testMapList()
    {
        $url = '/api/statistics/issues/overview';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->overview);
        $overview = $mapData->data->overview;

        $this->assertObjectHasAttribute('newIssuesCount', $overview);
        $this->assertObjectHasAttribute('openIssuesCount', $overview);
        $this->assertObjectHasAttribute('markedIssuesCount', $overview);
        $this->assertObjectHasAttribute('overdueIssuesCount', $overview);
        $this->assertObjectHasAttribute('respondedNotReviewedIssuesCount', $overview);
    }
}
