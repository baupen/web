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
use App\Report\PdfDefinition;
use App\Report\Report;
use App\Report\ReportElements;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ReportService constructor.
     *
     * @param ImageServiceInterface $imageService
     * @param RegistryInterface $registry
     * @param SerializerInterface $serializer
     */
    public function __construct(ImageServiceInterface $imageService, RegistryInterface $registry, SerializerInterface $serializer, TranslatorInterface $translator)
    {
        $this->imageService = $imageService;
        $this->doctrine = $registry;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     * @param ReportElements $elements
     * @param Issue[] $issues
     * @param string $filePath
     */
    private function render(ConstructionSite $constructionSite, Filter $filter, string $author, ReportElements $elements, array $issues, string $filePath)
    {
        // initialize report
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $author, __DIR__ . '/../../public/files/report_logo.png');
        $report = new Report($pdfDefinition);

        $this->addIntroduction($report, $constructionSite, $filter);

        //add tables
        if ($elements->getTableByCraftsman()) {
            $report->addTableByCraftsman($issues);
        }
        if ($elements->getTableByMap()) {
            $this->addTableByMap($report, $filter, $issues);
        }
        if ($elements->getTableByTrade()) {
            $report->addTableByTrade($issues);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];
            $report->addMap($map, $this->imageService->generateMapImage($map, $issues), $issuesPerMap[$map->getId()]);
        }

        $report->save($filePath);
    }

    private function addIntroduction(Report $report, ConstructionSite $constructionSite, Filter $filter)
    {
        $filterEntries = [];
        $report->addIntroduction($constructionSite, $filterEntries);
    }

    private function addTableByMap(Report $report, Filter $filter, $issues)
    {
        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //count issue status per map
        $countsPerMap = [];
        foreach ($orderedMaps as $orderedMap) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerMap[$orderedMap->getId()] as $issue) {
                if ($issue->getStatusCode() >= Issue::REVIEW_STATUS) {
                    ++$countPerMap[2];
                } elseif ($issue->getStatusCode() >= Issue::RESPONSE_STATUS) {
                    ++$countPerMap[1];
                } else {
                    ++$countPerMap[0];
                }
            }
            $countsPerMap[$orderedMap->getId()] = $countPerMap;
        }

        $tableHeader[] = $this->translator->trans('entity.name', [], 'entity_map');
        $tableHeader[] = $this->translator->trans('context', [], 'entity_map');
        $tableContent = [];

        //add map name & map context to table
        foreach ($countsPerMap as $mapId => $count) {
            $tableContent[$mapId] = [];
            $tableContent[$mapId][] = $orderedMaps[$mapId]->getName();
            $tableContent[$mapId][] = $orderedMaps[$mapId]->getContext();
        }

        //add registration count if filter did not exclude
        if ($filter->getRegistrationStatus() === null || $filter->getRegistrationStatus()) {
            $tableHeader[] = $this->translator->trans('status.registered', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[0];
            }
        }

        //add response count if filter did not exclude
        if ($filter->getRespondedStatus() === null || $filter->getRespondedStatus()) {
            $tableHeader[] = $this->translator->trans('status.responded', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[1];
            }
        }

        //add review count if filter did not exclude
        if ($filter->getReviewedStatus() === null || $filter->getReviewedStatus()) {
            $tableHeader[] = $this->translator->trans('status.reviewed', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[2];
            }
        }

        $report->addTable($tableHeader, $tableContent);
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     * @param ReportElements $elements
     *
     * @return string
     */
    public function generateReport(ConstructionSite $constructionSite, Filter $filter, string $author, ReportElements $elements)
    {
        $issues = $this->doctrine->getRepository(Issue::class)->filter($filter);

        //create folder
        $generationTargetFolder = $this->getGenerationTargetFolder($constructionSite);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        //only generate report if it does not already exist
        $filePath = $this->getPathFor($constructionSite);
        if (!file_exists($filePath) || true) {
            $author = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format('c'), '%name%' => $author], 'report');
            $this->render($constructionSite, $filter, $author, $elements, $issues, $filePath);
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
        //$filename = uniqid() . '.pdf';
        $filename = 'trial.pdf';

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
