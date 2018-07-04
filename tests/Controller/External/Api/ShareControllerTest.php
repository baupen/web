<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Request\Share\IssueRequest;
use App\Entity\Craftsman;
use App\Enum\ApiStatus;
use App\Tests\Controller\External\Api\Base\ApiController;
use Doctrine\ORM\ORMException;

class ShareControllerTest extends ApiController
{
    /**
     * @var Craftsman|null
     */
    private $craftsman = null;

    /**
     * @param $relativeLink
     * @param null $payload
     *
     * @throws ORMException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function authenticatedRequest($relativeLink, $payload = null)
    {
        $client = $this->getClient();

        if ($this->craftsman === null) {
            /** @var Craftsman $craftsman */
            $this->craftsman = $client->getContainer()->get("doctrine")->getRepository(Craftsman::class)->findOneBy([]);
            if ($this->craftsman->getEmailIdentifier() === null) {
                $this->craftsman->setEmailIdentifier();
                $manager = $client->getContainer()->get("doctrine.orm.entity_manager");
                $manager->flush($this->craftsman);
            }
        }


        $url = '/external/api/share/c/' . $this->craftsman->getEmailIdentifier() . $relativeLink;
        if ($payload === null) {
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
     * @throws ORMException
     */
    public function testMapsList()
    {
        $response = $this->authenticatedRequest("/maps/list");
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->maps);

        $this->assertTrue(is_array($mapData->data->maps));
        foreach ($mapData->data->maps as $map) {
            $this->assertNotNull($map);
            $this->assertObjectHasAttribute("name", $map);
            $this->assertObjectHasAttribute("context", $map);
            $this->assertObjectHasAttribute("imageFilePath", $map);

            $this->assertTrue(is_array($map->issues));
            foreach ($map->issues as $issue) {
                $this->assertObjectHasAttribute("registeredAt", $issue);
                $this->assertObjectHasAttribute("registrationByName", $issue);
                $this->assertObjectHasAttribute("description", $issue);
                $this->assertObjectHasAttribute("imageFilePath", $issue);
                $this->assertObjectHasAttribute("responseLimit", $issue);
                $this->assertObjectHasAttribute("id", $issue);
            }
        }
    }

    /**
     * @throws ORMException
     */
    public function testRespond()
    {
        $response = $this->authenticatedRequest("/maps/list");
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $issue = $mapData->data->maps[0]->issues[0];
        $request = new IssueRequest();
        $request->setIssueId($issue->id);

        //execute issue action; indicate whether request was successful or skipped
        $doRequest = function ($action, $skipped) use ($request) {
            $response = $this->authenticatedRequest("/issue/" . $action, $request);
            $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);
            if ($skipped) {
                $this->assertTrue(count($mapData->data->skippedIds) === 1);
            } else {
                $this->assertTrue(count($mapData->data->successfulIds) === 1);
            }
        };

        $doRequest("respond", false);
        $doRequest("respond", true);
        $doRequest("respond", true);

        $doRequest("remove_response", false);
        $doRequest("remove_response", true);

        $doRequest("respond", false);
    }
}