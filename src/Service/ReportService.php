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

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\IssueHelper;
use App\Report\Pdf;
use App\Report\Report;
use App\Report\ReportDefinition;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ReportService implements ReportServiceInterface
{
    /**
     * @var string
     */
    private $pubFolder = __DIR__ . '/../../public';

    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ReportService constructor.
     *
     * @param ImageServiceInterface $imageService
     * @param RegistryInterface $registry
     * @param SerializerInterface $serializer
     */
    public function __construct(ImageServiceInterface $imageService, RegistryInterface $registry, SerializerInterface $serializer)
    {
        $this->imageService = $imageService;
        $this->doctrine = $registry;
        $this->serializer = $serializer;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param array $issues
     * @param string $author
     * @param string $filePath
     */
    private function render(ConstructionSite $constructionSite, Filter $filter, string $author, array $issues, string $filePath)
    {
        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //define report
        $report = new ReportDefinition($constructionSite, $filter, $author);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];
            $report->addMap($map, $issues, $this->imageService->generateMapImage($map, $issues));
        }

        //create report & generate pdf
        $pdf = new Report($report);
        $pdf->generate($filePath);
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     *
     * @return string
     */
    public function generateReport(ConstructionSite $constructionSite, Filter $filter, string $author)
    {
        $issues = $this->doctrine->getRepository(Issue::class)->filter($filter);

        //create folder
        $generationTargetFolder = $this->getGenerationTargetFolder($constructionSite);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        //only generate report if it does not already exist
        $filePath = $this->getPathFor($constructionSite);
        if (!file_exists($filePath)) {
            $this->render($constructionSite, $filter, $author, $issues, $filePath);
        }

        return $filePath;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param array $issues
     *
     * @return string
     */
    private function getPathFor(ConstructionSite $constructionSite)
    {
        //consider changing the filename to hash input values of the generation
        $filename = uniqid() . '.pdf';

        return $this->getGenerationTargetFolder($constructionSite) . '/' . $filename;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    private function getGenerationTargetFolder(ConstructionSite $constructionSite)
    {
        return $this->pubFolder . '/generated/' . $constructionSite->getId() . '/report';
    }
}
