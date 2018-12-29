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
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\PdfDefinition;
use App\Service\Report\Report;
use App\Service\Report\ReportConfiguration;
use App\Service\Report\ReportElements;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReportService implements ReportServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

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
     * @var bool
     */
    private $disableCache = false;

    /**
     * ReportService constructor.
     *
     * @param ImageServiceInterface $imageService
     * @param RegistryInterface $registry
     * @param SerializerInterface $serializer
     * @param TranslatorInterface $translator
     * @param PathServiceInterface $pathService
     */
    public function __construct(ImageServiceInterface $imageService, RegistryInterface $registry, SerializerInterface $serializer, TranslatorInterface $translator, PathServiceInterface $pathService)
    {
        $this->imageService = $imageService;
        $this->doctrine = $registry;
        $this->serializer = $serializer;
        $this->translator = $translator;
        $this->pathService = $pathService;
    }

    /**
     * @param Report $report
     * @param Map $map
     * @param Issue[] $issues
     */
    private function addMap(Report $report, Map $map, array $issues)
    {
        $mapImage = $this->imageService->generateMapImageForReport($map, $issues, ImageServiceInterface::SIZE_REPORT_MAP);
        $report->addMap($map->getName(), $map->getContext(), $mapImage);
    }

    /**
     * @param Report $report
     * @param Issue[] $issues
     */
    private function addIssueImageGrid(Report $report, array $issues)
    {
        $columnCount = 4;

        $imageGrid = [];
        $currentRow = [];
        foreach ($issues as $issue) {
            $currentIssue = [];

            $imagePath = $this->imageService->getSizeForIssue($issue, ImageServiceInterface::SIZE_REPORT_ISSUE);
            if ($imagePath === null) {
                continue;
            }

            $currentIssue['imagePath'] = $imagePath;
            $currentIssue['identification'] = $issue->getNumber();
            $currentRow[] = $currentIssue;

            //add row to grid if applicable
            if (\count($currentRow) === $columnCount) {
                $imageGrid[] = $currentRow;
                $currentRow = [];
            }
        }
        if (\count($currentRow) > 0) {
            $imageGrid[] = $currentRow;
        }

        $report->addImageGrid($imageGrid, $columnCount);
    }

    /**
     * @param Report $report
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param ReportElements $reportElements
     */
    private function addIntroduction(Report $report, ConstructionSite $constructionSite, Filter $filter, ReportElements $reportElements)
    {
        $filterEntries = $this->getFilterEntries($filter);

        //add list of elements which are part of this report
        $elements = [];
        if ($reportElements->getTableByCraftsman()) {
            $elements[] = $this->translator->trans('table.by_craftsman', [], 'report');
        }
        if ($reportElements->getTableByMap()) {
            $elements[] = $this->translator->trans('table.by_map', [], 'report');
        }
        if ($reportElements->getTableByTrade()) {
            $elements[] = $this->translator->trans('table.by_trade', [], 'report');
        }
        $elements[] = $this->translator->trans('issues.detailed', [], 'report');
        if ($reportElements->getWithImages()) {
            $elements[\count($elements) - 1] .= ' ' . $this->translator->trans('issues.with_images', [], 'report');
        }

        //print
        $report->addIntroduction(
            $this->imageService->getSizeForConstructionSite($constructionSite, ImageServiceInterface::SIZE_REPORT_ISSUE),
            $constructionSite->getName(),
            implode("\n", $constructionSite->getAddressLines()),
            implode(', ', $elements),
            $filterEntries,
            $this->translator->trans('entity.name', [], 'entity_filter')
        );
    }

    /**
     * @param Report $report
     * @param Filter $filter
     * @param Issue[] $issues
     */
    private function addIssueTable(Report $report, Filter $filter, array $issues)
    {
        $showRegistered = $filter->getRegistrationStatus() === null || $filter->getRegistrationStatus();
        $showResponded = $filter->getRespondedStatus() === null || $filter->getRespondedStatus();
        $showReviewed = $filter->getReviewedStatus() === null || $filter->getReviewedStatus();

        $tableHeader[] = '#';
        $tableHeader[] = $this->translator->trans('description', [], 'entity_issue');
        $tableHeader[] = $this->translator->trans('response_limit', [], 'entity_issue');

        $tableContent = [];
        foreach ($issues as $issue) {
            $row = [];
            $row[] = $issue->getNumber();
            $row[] = $issue->getDescription();
            $row[] = ($issue->getResponseLimit() !== null) ? $issue->getResponseLimit()->format(DateTimeFormatter::DATE_FORMAT) : '';
        }

        $issueCount = \count($issues);
        if ($showRegistered) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.registered', [], 'entity_issue')], 'report');
            for ($i = 0; $i < $issueCount; ++$i) {
                $issue = $issues[$i];
                $tableContent[$i][] = $issue->getRegisteredAt() === null ? '' : $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getRegistrationBy()->getName();
            }
        }

        if ($showResponded) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.responded', [], 'entity_issue')], 'report');
            for ($i = 0; $i < $issueCount; ++$i) {
                $issue = $issues[$i];
                $tableContent[$i][] = $issue->getRespondedAt() === null ? '' : $issue->getRespondedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getResponseBy()->getName();
            }
        }

        if ($showReviewed) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.reviewed', [], 'entity_issue')], 'report');
            for ($i = 0; $i < $issueCount; ++$i) {
                $issue = $issues[$i];
                $tableContent[$i][] = $issue->getReviewedAt() === null ? '' : $issue->getReviewedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getReviewBy()->getName();
            }
        }

        $report->addTable($tableHeader, $tableContent);
    }

    /**
     * @param Filter $filter
     * @param array $orderedMaps
     * @param Issue[][] $issuesPerMap
     * @param $tableContent
     * @param $tableHeader
     */
    private function addAggregatedIssuesInfo(Filter $filter, array $orderedMaps, array $issuesPerMap, array &$tableContent, array &$tableHeader)
    {
        //count issue status per map
        $countsPerElement = [];
        foreach ($orderedMaps as $index => $element) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerMap[$index] as $issue) {
                if ($issue->getStatusCode() >= Issue::REVIEW_STATUS) {
                    ++$countPerMap[2];
                } elseif ($issue->getStatusCode() >= Issue::RESPONSE_STATUS) {
                    ++$countPerMap[1];
                } else {
                    ++$countPerMap[0];
                }
            }
            $countsPerElement[$index] = $countPerMap;
        }

        //add registration count if filter did not exclude
        if ($filter->getRegistrationStatus() === null || $filter->getRegistrationStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.registered', [], 'entity_issue');
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[0];
            }
        }

        //add response count if filter did not exclude
        if ($filter->getRespondedStatus() === null || $filter->getRespondedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.responded', [], 'entity_issue');
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[1];
            }
        }

        //add review count if filter did not exclude
        if ($filter->getReviewedStatus() === null || $filter->getReviewedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[2];
            }
        }
    }

    /**
     * @param Report $report
     * @param Filter $filter
     * @param Issue[] $issues
     */
    private function addTableByMap(Report $report, Filter $filter, array $issues)
    {
        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('context', [], 'entity_map'), $this->translator->trans('entity.name', [], 'entity_map')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedMaps as $mapId => $map) {
            $tableContent[$mapId] = [$map->getContext(), $map->getName()];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedMaps, $issuesPerMap, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_map', [], 'report'));
    }

    /**
     * @param Report $report
     * @param Filter $filter
     * @param Issue[] $issues
     */
    private function addTableByCraftsman(Report $report, Filter $filter, array $issues)
    {
        /* @var Craftsman[] $orderedCraftsman */
        /* @var Issue[][] $issuesPerCraftsman */
        IssueHelper::issuesToOrderedCraftsman($issues, $orderedCraftsman, $issuesPerCraftsman);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('entity.name', [], 'entity_craftsman')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedCraftsman as $craftsmanId => $craftsman) {
            $tableContent[$craftsmanId] = [$craftsman->getName()];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedCraftsman, $issuesPerCraftsman, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_craftsman', [], 'report'));
    }

    /**
     * @param Report $report
     * @param Filter $filter
     * @param Issue[] $issues
     */
    private function addTableByTrade(Report $report, Filter $filter, array $issues)
    {
        /* @var string[] $orderedTrade */
        /* @var Issue[][] $issuesPerTrade */
        IssueHelper::issuesToOrderedTrade($issues, $orderedTrade, $issuesPerTrade);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('trade', [], 'entity_craftsman')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedTrade as $trade) {
            $tableContent[$trade] = [$trade];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedTrade, $issuesPerTrade, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_trade', [], 'report'));
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     * @param ReportElements $reportElements
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateReport(ConstructionSite $constructionSite, Filter $filter, string $author, ReportElements $reportElements)
    {
        $issues = $this->doctrine->getRepository(Issue::class)->filter($filter);
        $reportConfiguration = new ReportConfiguration($filter);

        //only generate report if it does not already exist
        $filePath = $this->getFilePath($constructionSite);
        if (file_exists($filePath) && !$this->disableCache) {
            return $filePath;
        }

        // initialize report
        $footnote = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $footnote, __DIR__ . '/../../public/files/report_logo.png');
        $report = new Report($pdfDefinition);

        $this->addIntroduction($report, $constructionSite, $filter, $reportElements);

        //add tables
        if ($reportElements->getTableByCraftsman()) {
            $this->addTableByCraftsman($report, $filter, $issues);
        }
        if ($reportElements->getTableByMap()) {
            $this->addTableByMap($report, $filter, $issues);
        }
        if ($reportElements->getTableByTrade()) {
            $this->addTableByTrade($report, $filter, $issues);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];

            $this->addMap($report, $map, $issues);
            $this->addIssueTable($report, $filter, $issues);

            if ($reportElements->getWithImages()) {
                $this->addIssueImageGrid($report, $issues);
            }
        }

        $report->save($filePath);

        return $filePath;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    private function getFilePath(ConstructionSite $constructionSite)
    {
        //create folder
        $generationTargetFolder = $this->pathService->getTransientFolderForReports($constructionSite);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        return $generationTargetFolder . \DIRECTORY_SEPARATOR . uniqid() . '.pdf';
    }

    /**
     * @param Filter $filter
     *
     * @return array
     */
    private function getFilterEntries(Filter $filter): array
    {
        $filterEntries = [];

        $statusLabel = $this->translator->trans('status', [], 'entity_issue');
        $filterEntries[$statusLabel] = $this->getStatusFilterEntry($filter);

        //add craftsmen
        if ($filter->getCraftsmen() !== null) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmen()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName();
            }

            $label = $this->translator->transChoice('filter.craftsmen', \count($names), [], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        //add maps
        if ($filter->getMaps() !== null) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMaps()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName() . ' (' . $item->getContext() . ')';
            }

            $label = $this->translator->transChoice('filter.maps', \count($names), [], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        //add limit
        if ($filter->getResponseLimitStart() !== null || $filter->getResponseLimitEnd() !== null) {
            $limitValue = $this->dateTimeRangeToText($filter->getResponseLimitStart(), $filter->getResponseLimitEnd());
            $label = $this->translator->trans('response_limit', [], 'entity_issue');
            $filterEntries[$label] = $limitValue;
        }

        //add number
        if ($filter->getNumber() !== null) {
            $label = $this->translator->trans('number', [], 'entity_issue');
            $filterEntries[$label] = $filter->getNumber();
        }

        //add trades
        if ($filter->getTrades() !== null) {
            $names = [];
            foreach ($filter->getTrades() as $item) {
                $names[] = $item;
            }

            $label = $this->translator->transChoice('filter.trades', \count($names), [], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        return $filterEntries;
    }

    /**
     * @param Filter $filter
     *
     * @return string
     */
    private function getStatusFilterEntry(Filter $filter): string
    {
        // collect all set status
        $statusEntries = [];
        if ($filter->getRegistrationStatus() !== null) {
            $label = $this->translator->trans('status_values.registered', [], 'entity_issue');
            $statusEntries[] = $this->statusToString($label, $filter->getRegistrationStatus() === false);
        }

        if ($filter->getRespondedStatus() !== null) {
            $label = $this->translator->trans('status_values.responded', [], 'entity_issue');
            $statusEntries[] = $this->statusToString($label, $filter->getRespondedStatus() === false, $filter->getRespondedStart(), $filter->getRespondedEnd());
        }

        if ($filter->getReviewedStatus() !== null) {
            $label = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            $statusEntries[] = $this->statusToString($label, $filter->getReviewedStatus() === false, $filter->getReviewedStart(), $filter->getRespondedEnd());
        }

        // try to simplify
        if (\count($statusEntries) === 0) {
            return $this->translator->trans('status_values.none', [], 'entity_issue');
        } elseif (\count($statusEntries) === 3 &&
            $filter->getRespondedStart() === null && $filter->getRespondedEnd() === null &&
            $filter->getReviewedStart() === null && $filter->getReviewedEnd() === null) {
            return $this->translator->trans('status_values.all', [], 'entity_issue');
        }

        return implode(', ', $statusEntries);
    }

    /**
     * @param string $label
     * @param bool|null $negate
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return string
     */
    private function statusToString(string $label, ?bool $negate, \DateTime $start = null, \DateTime $end = null)
    {
        $result = $label;

        if ($start !== null || $end !== null) {
            $result = $label . ' ' . $this->dateTimeRangeToText($start, $end);
        }

        if ($negate) {
            $result = $this->translator->trans('filter.not', ['%state%' => $result], 'report');
        }

        return $result;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return string
     */
    private function dateTimeRangeToText(\DateTime $start = null, \DateTime $end = null)
    {
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
    }
}
