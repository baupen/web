<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\SampleServiceInterface;
use App\Service\Interfaces\TrialServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrialService implements TrialServiceInterface
{
    /**
     * @var SampleServiceInterface
     */
    private $sampleService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * TrialService constructor.
     */
    public function __construct(TranslatorInterface $translator, SampleServiceInterface $sampleService)
    {
        $this->translator = $translator;
        $this->sampleService = $sampleService;
    }

    public function createTrialConstructionSite(ConstructionManager $constructionManager): ConstructionSite
    {
        $constructionSite = $this->sampleService->createSampleConstructionSite(SampleServiceInterface::SAMPLE_SIMPLE, $constructionManager);
        $constructionSite->setName($this->translator->trans('example.name', ['%name%' => $constructionManager->getName()], 'entity_construction_site'));
        $constructionSite->setStreetAddress($this->translator->trans('example.street_address', [], 'entity_construction_site'));
        $constructionSite->setLocality($this->translator->trans('example.locality', [], 'entity_construction_site'));
        $constructionSite->setPostalCode((int) $this->translator->trans('example.postal_code', [], 'entity_construction_site'));
        $constructionSite->setCountry($this->translator->trans('example.country', [], 'entity_construction_site'));
        $constructionSite->setIsTrialConstructionSite(true);

        $constructionSite->getConstructionManagers()->add($constructionManager);
        $constructionManager->getConstructionSites()->add($constructionSite);

        return $constructionSite;
    }
}
