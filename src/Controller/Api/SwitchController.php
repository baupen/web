<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Api\Request\_Switch\CreateCheckRequest;
use App\Api\Request\_Switch\CreateRequest;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Response\Data\_Switch\CheckData;
use App\Api\Response\Data\ConstructionSitesData;
use App\Api\Response\Data\EmptyData;
use App\Api\Transformer\Switch_\ConstructionSiteTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/switch")
 */
class SwitchController extends ApiController
{
    const CONSTRUCTION_SITE_NAME_TAKEN = 'This construction site name is already taken';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * throws an exception if a trial account is used to authenticate.
     */
    private function ensureNoTrialAccount()
    {
        if ($this->getUser()->getIsTrialAccount()) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @Route("/construction_sites", name="api_switch_constrution_sites")
     *
     * @param ConstructionSiteTransformer $constructionSiteTransformer
     *
     * @return Response
     */
    public function constructionSitesAction(ConstructionSiteTransformer $constructionSiteTransformer)
    {
        $this->ensureNoTrialAccount();

        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false]);

        //create response
        $data = new ConstructionSitesData();
        $data->setConstructionSites($constructionSiteTransformer->toApiMultiple($constructionSites));

        return $this->success($data);
    }

    /**
     * @Route("/request_access", name="api_switch_request_access")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function requestAccessAction(Request $request)
    {
        $this->ensureNoTrialAccount();

        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false, 'id' => $parsedRequest->getConstructionSiteId()]);
        if (\count($constructionSites) === 0) {
            return $this->fail(self::CONSTRUCTION_SITE_NOT_FOUND);
        }

        $constructionSite = $constructionSites[0];
        if (!$constructionSite->getConstructionManagers()->contains($this->getUser())) {
            $constructionSite->getConstructionManagers()->add($this->getUser());
            $this->fastSave($constructionSite);
        }

        return $this->success(new EmptyData());
    }

    /**
     * @Route("/remove_access", name="api_switch_remove_access")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeAccessAction(Request $request)
    {
        $this->ensureNoTrialAccount();

        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false, 'id' => $parsedRequest->getConstructionSiteId()]);
        if (\count($constructionSites) === 0) {
            return $this->fail(self::CONSTRUCTION_SITE_NOT_FOUND);
        }

        $constructionSite = $constructionSites[0];
        if ($constructionSite->getConstructionManagers()->contains($this->getUser())) {
            $constructionSite->getConstructionManagers()->removeElement($this->getUser());
            $this->fastSave($constructionSite);
        }

        return $this->success(new EmptyData());
    }

    /**
     * @Route("/create/check", name="api_switch_create/check")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createCheckAction(Request $request)
    {
        $this->ensureNoTrialAccount();

        /** @var CreateCheckRequest $parsedRequest */
        if (!parent::parseRequest($request, CreateCheckRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        $constructionSiteExists = $this->checkIfConstructionSiteExists($parsedRequest->getConstructionSiteName());

        // response
        $checkData = new CheckData();
        $checkData->setConstructionSiteNameTaken($constructionSiteExists);

        return $this->success($checkData);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function checkIfConstructionSiteExists(string $name)
    {
        /** @var ConstructionSite $constructionSite */
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['name' => $name]);

        return \count($constructionSites) > 0;
    }

    /**
     * @Route("/create", name="api_switch_create")
     *
     * @param Request $request
     * @param PathServiceInterface $pathService
     *
     * @return Response
     */
    public function createAction(Request $request, PathServiceInterface $pathService)
    {
        $this->ensureNoTrialAccount();

        /** @var CreateRequest $parsedRequest */
        if (!parent::parseRequest($request, CreateRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // ensure not multiple construction sites exist with the same name
        if ($this->checkIfConstructionSiteExists($parsedRequest->getName())) {
            return $this->fail(self::CONSTRUCTION_SITE_NAME_TAKEN);
        }

        // create new construction site
        $constructionSite = new ConstructionSite();
        $constructionSite->setName($parsedRequest->getName());
        $constructionSite->setStreetAddress($parsedRequest->getStreetAddress());
        $constructionSite->setPostalCode($parsedRequest->getPostalCode());
        $constructionSite->setLocality($parsedRequest->getLocality());
        $this->setFolderName($constructionSite, $pathService);
        $constructionSite->getConstructionManagers()->add($this->getUser());

        // switch the use to this site
        $user = $this->getUser();
        $user->setActiveConstructionSite($constructionSite);

        // save & redirect to edit page
        $this->fastSave($constructionSite, $user);

        return $this->redirectToRoute('edit');
    }

    /**
     * @param string $userInput
     *
     * @return string
     */
    private function sanitizeFolderName(string $userInput)
    {
        // only keep A-Z, a-z, 0-9
        $folderName = preg_replace('/[^A-Za-z0-9]+/', '-', $userInput);
        if (\mb_strlen($folderName) > 40) {
            return mb_substr($folderName, 0, 40);
        }

        return $folderName;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param PathServiceInterface $pathService
     */
    private function setFolderName(ConstructionSite $constructionSite, PathServiceInterface $pathService)
    {
        $proposedFolderName = $this->sanitizeFolderName($constructionSite->getName());
        $rootPath = $pathService->getConstructionSiteFolderRoot();

        $checkedFolderName = $proposedFolderName;
        $index = 1;
        while (is_dir($rootPath . \DIRECTORY_SEPARATOR . $checkedFolderName)) {
            $checkedFolderName = $proposedFolderName . $index++;
        }

        $constructionSite->setFolderName($checkedFolderName);
    }
}
