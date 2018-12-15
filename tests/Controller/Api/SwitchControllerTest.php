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

class SwitchControllerTest extends ApiController
{
    public function testConstructionSiteList()
    {
        $url = '/api/switch/construction_sites';

        $response = $this->authenticatedGetRequest($url);
        $constructionSiteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($constructionSiteData->data);
        $this->assertTrue(\is_array($constructionSiteData->data->constructionSites));
        $this->assertTrue(\count($constructionSiteData->data->constructionSites) > 0);
        foreach ($constructionSiteData->data->constructionSites as $constructionSite) {
            $this->assertNotNull($constructionSite);
            $this->assertObjectHasAttribute('name', $constructionSite);
            $this->assertObjectHasAttribute('imageMedium', $constructionSite);
            $this->assertObjectHasAttribute('isConstructionManagerOf', $constructionSite);
            $this->assertObjectHasAttribute('constructionManagers', $constructionSite);
            $this->assertObjectHasAttribute('address', $constructionSite);
        }
    }

    public function testRequestRemoveAccess()
    {
        $constructionSite = $this->getSomeConstructionSite();

        $this->requestAccess($constructionSite->getId());
        $this->checkContainsWithSpecificAccess($constructionSite->getId(), true);

        $this->removeAccess($constructionSite->getId());
        $this->checkContainsWithSpecificAccess($constructionSite->getId(), false);

        $this->requestAccess($constructionSite->getId());
        $this->checkContainsWithSpecificAccess($constructionSite->getId(), true);

        $this->removeAccess($constructionSite->getId());
        $this->checkContainsWithSpecificAccess($constructionSite->getId(), false);
    }

    private function requestAccess($constructionSiteId)
    {
        $requestAccessUrl = '/api/switch/request_access';

        $request = new ConstructionSiteRequest();
        $request->setConstructionSiteId($constructionSiteId);
        $response = $this->authenticatedPostRequest($requestAccessUrl, $request);
        $this->checkResponse($response, ApiStatus::SUCCESS);
    }

    private function removeAccess($constructionSiteId)
    {
        $requestAccessUrl = '/api/switch/remove_access';

        $request = new ConstructionSiteRequest();
        $request->setConstructionSiteId($constructionSiteId);
        $response = $this->authenticatedPostRequest($requestAccessUrl, $request);
        $this->checkResponse($response, ApiStatus::SUCCESS);
    }

    private function checkContainsWithSpecificAccess($constructionSiteId, $expectedIsConstructionManagerOf)
    {
        $constructionSitesUrl = '/api/switch/construction_sites';
        $response = $this->authenticatedGetRequest($constructionSitesUrl);
        $constructionSiteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        foreach ($constructionSiteData->data->constructionSites as $constructionSite) {
            if ($constructionSite->id === $constructionSiteId) {
                $this->assertSame($expectedIsConstructionManagerOf, $constructionSite->isConstructionManagerOf);

                return;
            }
        }
        $this->fail('construction site not found');
    }
}
