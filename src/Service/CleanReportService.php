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
use App\Service\Report\Document\Interfaces\DocumentServiceInterface;
use App\Service\Report\Interfaces\IssueReportServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentServiceInterface;
use App\Service\Report\Report;
use App\Service\Report\ReportConfiguration;
use App\Service\Report\ReportElements;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CleanReportService
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var DocumentServiceInterface
     */
    private $documentService;

    /**
     * @var IssueReportServiceInterface
     */
    private $issueReport;

    /**
     * ReportService constructor.
     *
     * @param ImageServiceInterface $imageService
     * @param RegistryInterface $registry
     * @param TranslatorInterface $translator
     * @param PathServiceInterface $pathService
     * @param PdfDocumentServiceInterface $documentService
     * @param IssueReportServiceInterface $issueReport
     */
    public function __construct(ImageServiceInterface $imageService, RegistryInterface $registry, TranslatorInterface $translator, PathServiceInterface $pathService, PdfDocumentServiceInterface $documentService, IssueReportServiceInterface $issueReport)
    {
        $this->imageService = $imageService;
        $this->doctrine = $registry;
        $this->translator = $translator;
        $this->pathService = $pathService;
        $this->documentService = $documentService;
        $this->issueReport = $issueReport;
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

        // initialize report
        $logoPath = $this->pathService->getAssetsRoot() . \DIRECTORY_SEPARATOR . 'report' . \DIRECTORY_SEPARATOR . 'logo.png';
        $document = $this->documentService->create($constructionSite->getName(), $author, $logoPath);

        $this->addIntroduction($document, $constructionSite, $filter, $reportElements);

        //add tables
        if ($reportElements->getTableByCraftsman()) {
            $this->addTableByCraftsman($document, $issues, $reportConfiguration);
        }
        if ($reportElements->getTableByMap()) {
            $this->addTableByMap($document, $issues, $reportConfiguration);
        }
        if ($reportElements->getTableByTrade()) {
            $this->addTableByTrade($document, $issues, $reportConfiguration);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];

            $this->addMap($document, $map, $issues, $reportElements, $reportConfiguration);
        }

        $filePath = $this->getFilePath($constructionSite);
        $document->save($filePath);

        return $filePath;
    }

    /**
     * @param DocumentInterface $document
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param ReportElements $reportElements
     */
    private function addIntroduction(DocumentInterface $document, ConstructionSite $constructionSite, Filter $filter, ReportElements $reportElements)
    {
        $filterEntries = $this->getFilterEntries($filter);
        $reportElements = $this->getReportElements($reportElements);
        $constructionSiteImage = $this->imageService->getSizeForConstructionSite($constructionSite, ImageServiceInterface::SIZE_REPORT_ISSUE);
        $constructionSiteAddressLines = implode("\n", $constructionSite->getAddressLines());

        //print
        $this->issueReport->addIntroduction(
            $document,
            $constructionSite->getName(),
            $constructionSiteImage,
            $constructionSiteAddressLines,
            $reportElements,
            $filterEntries
        );
    }

    /**
     * @param DocumentInterface $document
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     */
    private function addTableByCraftsman(DocumentInterface $document, array $issues, ReportConfiguration $reportConfiguration)
    {
        $tableDescription = $this->translator->trans('table.by_craftsman', [], 'report');

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

        $this->addAggregatedIssuesTable($document, $reportConfiguration, $orderedCraftsman, $issuesPerCraftsman, $tableDescription, $tableHeader, $tableContent);
    }

    /**
     * @param DocumentInterface $document
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     */
    private function addTableByMap(DocumentInterface $document, array $issues, ReportConfiguration $reportConfiguration)
    {
        $tableDescription = $this->translator->trans('table.by_map', [], 'report');

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

        $this->addAggregatedIssuesTable($document, $reportConfiguration, $orderedMaps, $issuesPerMap, $tableDescription, $tableHeader, $tableContent);
    }

    /**
     * @param DocumentInterface $document
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     */
    private function addTableByTrade(DocumentInterface $document, array $issues, ReportConfiguration $reportConfiguration)
    {
        $tableDescription = $this->translator->trans('table.by_trade', [], 'report');

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

        $this->addAggregatedIssuesTable($document, $reportConfiguration, $orderedTrade, $issuesPerTrade, $tableDescription, $tableHeader, $tableContent);
    }

    /**
     * @param DocumentInterface $document
     * @param ReportConfiguration $reportConfiguration
     * @param array $elements
     * @param array $issuesPerElement
     * @param string $tableDescription
     * @param array $tableHeader
     * @param array $tableContent
     */
    private function addAggregatedIssuesTable(DocumentInterface $document, ReportConfiguration $reportConfiguration, array $elements, array $issuesPerElement, string $tableDescription, array $tableHeader, array $tableContent): void
    {
        //add accumulated info
        $issuesHeader = $this->getAggregatedIssuesTableHeader($reportConfiguration);
        $issuesContent = $this->getAggregatedIssuesTableContent($elements, $issuesPerElement, $reportConfiguration);

        //write to pdf
        $this->issueReport->addAggregatedIssueTable(
            $document,
            $tableDescription,
            $tableHeader,
            $tableContent,
            $issuesHeader,
            $issuesContent
        );
    }

    /**
     * @param ReportConfiguration $configuration
     *
     * @return string[]
     */
    private function getAggregatedIssuesTableHeader(ReportConfiguration $configuration)
    {
        $tableHeader = [];

        //add registration count if filter did not exclude
        if ($configuration->showRegistrationStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.registered', [], 'entity_issue');
        }

        //add response count if filter did not exclude
        if ($configuration->showRespondedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.responded', [], 'entity_issue');
        }

        //add review count if filter did not exclude
        if ($configuration->showReviewedStatus()) {
            $tableHeader[] = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
        }

        return $tableHeader;
    }

    /**
     * @param array $elements
     * @param Issue[][] $issuesPerElement
     * @param ReportConfiguration $configuration
     *
     * @return string[][]
     */
    private function getAggregatedIssuesTableContent(array $elements, array $issuesPerElement, ReportConfiguration $configuration)
    {
        $tableContent = [];

        //count issue status per map
        $countsPerElement = [];
        foreach ($elements as $index => $element) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerElement[$index] as $issue) {
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
        if ($configuration->showRegistrationStatus()) {
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[0];
            }
        }

        //add response count if filter did not exclude
        if ($configuration->showRespondedStatus()) {
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[1];
            }
        }

        //add review count if filter did not exclude
        if ($configuration->showReviewedStatus()) {
            foreach ($countsPerElement as $elementId => $count) {
                $tableContent[$elementId][] = $count[2];
            }
        }

        return $tableContent;
    }

    /**
     * @param DocumentInterface $report
     * @param Map $map
     * @param Issue[] $issues
     * @param ReportElements $elements
     * @param ReportConfiguration $reportConfiguration
     */
    private function addMap(DocumentInterface $report, Map $map, array $issues, ReportElements $elements, ReportConfiguration $reportConfiguration)
    {
        $mapName = $map->getName();
        $mapContext = $map->getContext();
        $mapImage = $this->imageService->generateMapImageForReport($map, $issues, ImageServiceInterface::SIZE_REPORT_MAP);

        $issuesTableHeader = $this->getIssuesTableHeader($reportConfiguration);
        $issuesTableContent = $this->getIssuesTableContent($issues, $reportConfiguration);

        $images = $elements->getWithImages() ? $this->getIssueImages($issues) : [];

        $this->issueReport->addMap(
            $report,
            $mapName,
            $mapContext,
            $mapImage,
            $issuesTableHeader,
            $issuesTableContent,
            $images
        );
    }

    /**
     * @param Issue[] $issues
     *
     * @return string[][]
     */
    private function getIssueImages(array $issues)
    {
        $issueImages = [];
        foreach ($issues as $issue) {
            $currentIssue = [];

            $imagePath = $this->imageService->getSizeForIssue($issue, ImageServiceInterface::SIZE_REPORT_ISSUE);
            if ($imagePath === null) {
                continue;
            }

            $currentIssue['imagePath'] = $imagePath;
            $currentIssue['number'] = $issue->getNumber();
            $issueImages[] = $currentIssue;
        }

        return $issueImages;
    }

    /**
     * @param Report $report
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     *
     * @return string[][]
     */
    private function getIssuesTableContent(array $issues, ReportConfiguration $reportConfiguration)
    {
        $tableContent = [];
        foreach ($issues as $issue) {
            $row = [];
            $row[] = $issue->getNumber();
            $row[] = $issue->getDescription();
            $row[] = ($issue->getResponseLimit() !== null) ? $issue->getResponseLimit()->format(DateTimeFormatter::DATE_FORMAT) : '';

            if ($reportConfiguration->showRegistrationStatus()) {
                $row[] = $issue->getRegisteredAt() === null ? '' : $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getRegistrationBy()->getName();
            }

            if ($reportConfiguration->showRespondedStatus()) {
                $row[] = $issue->getRespondedAt() === null ? '' : $issue->getRespondedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getResponseBy()->getName();
            }

            if ($reportConfiguration->showReviewedStatus()) {
                $row[] = $issue->getReviewedAt() === null ? '' : $issue->getReviewedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getReviewBy()->getName();
            }

            $tableContent[] = $row;
        }

        return $tableContent;
    }

    /**
     * @param ReportConfiguration $reportConfiguration
     *
     * @return array
     */
    private function getIssuesTableHeader(ReportConfiguration $reportConfiguration)
    {
        $tableHeader[] = '#';
        $tableHeader[] = $this->translator->trans('description', [], 'entity_issue');
        $tableHeader[] = $this->translator->trans('response_limit', [], 'entity_issue');

        if ($reportConfiguration->showRegistrationStatus()) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.registered', [], 'entity_issue')], 'report');
        }

        if ($reportConfiguration->showRespondedStatus()) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.responded', [], 'entity_issue')], 'report');
        }

        if ($reportConfiguration->showReviewedStatus()) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.reviewed', [], 'entity_issue')], 'report');
        }

        return $tableHeader;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @throws \Exception
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

        $date = (new \DateTime())->format('Y-m-dTH_i');

        return $generationTargetFolder . \DIRECTORY_SEPARATOR . $date . '_' . uniqid() . '.pdf';
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

    /**
     * @param ReportElements $reportElements
     *
     * @return string
     */
    private function getReportElements(ReportElements $reportElements): string
    {
        if ($reportElements->getTableByCraftsman()) {
            $elements[] = $this->translator->trans('table.by_craftsman', [], 'report');
        }
        if ($reportElements->getTableByMap()) {
            $elements[] = $this->translator->trans('table.by_map', [], 'report');
        }
        if ($reportElements->getTableByTrade()) {
            $elements[] = $this->translator->trans('table.by_trade', [], 'report');
        }

        $issueDetailsLabel = $this->translator->trans('issues.detailed', [], 'report');
        if ($reportElements->getWithImages()) {
            $issueDetailsLabel .= ' ' . $this->translator->trans('issues.with_images', [], 'report');
        }
        $elements[] = $issueDetailsLabel;

        return implode(', ', $elements);
    }
}
