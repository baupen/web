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

use App\Api\Entity\Base\BaseEntity;
use App\Api\Response\Data\ConfigurationData;
use App\Controller\Api\Base\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/configuration")
 */
class ConfigurationController extends ApiController
{
    const NO_ACCESSIBLE_CONSTRUCTION_SITE = 'no accessible construction site';

    /**
     * @Route("", name="api_configuration", methods={"GET"})
     *
     * @return Response
     */
    public function configurationAction()
    {
        //ge tor set the active construction site
        if ($this->getUser()->getActiveConstructionSite() === null || !$this->getUser()->getActiveConstructionSite()->getConstructionManagers()->contains($this->getUser())) {
            if ($this->getUser()->getConstructionSites()->count() === 0) {
                return $this->fail(self::NO_ACCESSIBLE_CONSTRUCTION_SITE);
            }
            $this->getUser()->setActiveConstructionSite($this->getUser()->getConstructionSites()->get(0));
            $this->fastSave($this->getUser());
        }

        $data = new ConfigurationData();
        $data->setConstructionSite(new BaseEntity($this->getUser()->getActiveConstructionSite()->getId()));

        return $this->success($data);
    }
}
