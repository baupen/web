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
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\DateTimeFormatter;
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

        //appends the timings to the status and remembers if it has happend once (important later)
        $timeSpecified = false;
        $getDateTimeRange = function ($start = null, $end = null) use (&$timeSpecified) {
            $timeSpecified |= $start !== null || $end !== null;
            /** @var \DateTime|null $start */
            /* @var \DateTime|null $end */
            if ($start !== null) {
                if ($end !== null) {
                    return '(' . $start->format(DateTimeFormatter::DATE_TIME_FORMAT) . ' - ' . $end->format(DateTimeFormatter::DATE_TIME_FORMAT) . ')';
                }

                return '(' . $this->translator->trans('filter.later_than', ['%date%' => $start->format(DateTimeFormatter::DATE_TIME_FORMAT)], 'report') . ')';
            } elseif ($end !== null) {
                return '(' . $this->translator->trans('filter.earlier_than', ['%date%' => $end->format(DateTimeFormatter::DATE_TIME_FORMAT)], 'report') . ')';
            }

            return '';
        };

        //creates the status string
        $getStatus = function ($status, $trans, $start = null, $end = null) use ($getDateTimeRange) {
            if ($status) {
                return $trans . ' ' . $getDateTimeRange($start, $end);
            }

            return $this->translator->trans('filter.not', ['%state%' => $trans], 'report');
        };

        //collect all set status
        $statusEntries = [];
        if ($filter->getRegistrationStatus() !== null) {
            $trans = $this->translator->trans('status_values.registered', [], 'entity_issue');
            $statusEntries[] = $getStatus($filter->getRegistrationStatus(), $trans);
        }
        if ($filter->getRespondedStatus() !== null) {
            $trans = $this->translator->trans('status_values.responded', [], 'entity_issue');
            $statusEntries[] = $getStatus($filter->getRespondedStatus(), $trans, $filter->getRespondedStart(), $filter->getRespondedEnd());
        }
        if ($filter->getReviewedStatus() !== null) {
            $trans = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            $statusEntries[] = $getStatus($filter->getReviewedStatus(), $trans, $filter->getReviewedStart(), $filter->getRespondedEnd());
        }

        //convert all set status to a single string
        if (count($statusEntries) === 3 && !$timeSpecified) {
            //shorten
            $statusEntry = $this->translator->trans('status_values.all', [], 'entity_issue');
        } elseif (count($statusEntries) === 0) {
            $statusEntry = $this->translator->trans('status_values.none', [], 'entity_issue');
        } else {
            $statusEntry = implode(', ', $statusEntries);
        }
        $filterEntries[$this->translator->trans('status', [], 'entity_issue')] = $statusEntry;

        //add craftsmen
        if ($filter->getCraftsmen() !== null) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmen()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName();
            }
            $filterEntries[$this->translator->transChoice('filter.craftsmen', count($names), [], 'report')] = implode(', ', $names);
        }

        //add maps
        if ($filter->getMaps() !== null) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMaps()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName() . ' (' . $item->getContext() . ')';
            }
            $filterEntries[$this->translator->transChoice('filter.maps', count($names), [], 'report')] = implode(', ', $names);
        }

        //add limit
        $limitValue = $getDateTimeRange($filter->getResponseLimitStart(), $filter->getResponseLimitEnd());
        if ($limitValue !== '') {
            $filterEntries[$this->translator->transChoice('response_limit', count($names), [], 'entity_issue')] = $limitValue;
        }

        //set other properties
        //intentionally ignoring isMarked as this is part of the application, not the report
        if ($filter->getNumber() !== null) {
            $filterEntries[$this->translator->trans('number', [], 'entity_issue')] = $filter->getNumber();
        }
        if ($filter->getTrades() !== null) {
            $filterEntries[$this->translator->transChoice('filter.trades', count($filter->getTrades()), [], 'report')] = implode(', ', $filter->getTrades());
        }

        $report->addIntroduction($constructionSite, $filterEntries, $this->translator->trans('entity.name', [], 'entity_filter'));
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
            $tableHeader[] = $this->translator->trans('status_values.registered', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[0];
            }
        }

        //add response count if filter did not exclude
        if ($filter->getRespondedStatus() === null || $filter->getRespondedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.responded', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[1];
            }
        }

        //add review count if filter did not exclude
        if ($filter->getReviewedStatus() === null || $filter->getReviewedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            foreach ($countsPerMap as $mapId => $count) {
                $tableContent[$mapId][] = $count[2];
            }
        }

        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_map', [], 'report'));
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_map', [], 'report'));
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
