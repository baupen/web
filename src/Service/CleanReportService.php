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
use App\Service\Report\IssueReport\Interfaces\IssueReportServiceInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\IssueImage;
use App\Service\Report\IssueReport\Model\MapContent;
use App\Service\Report\IssueReport\PdfBuildingBlocks;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Interfaces\PdfFactoryInterface;
use App\Service\Report\Pdf\LayoutFactory;
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
     * @var LayoutServiceInterface
     */
    private $layoutService;

    /**
     * @var TypographyServiceInterface
     */
    private $typographyService;

    /**
     * @var ColorServiceInterface
     */
    private $colorService;

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
     * @var PdfFactoryInterface
     */
    private $pdfFactory;

    /**
     * @var IssueReportServiceInterface
     */
    private $issueReportService;

    /**
     * ReportService constructor.
     *
     * @param ImageServiceInterface $imageService
     * @param LayoutServiceInterface $layoutService
     * @param RegistryInterface $registry
     * @param TranslatorInterface $translator
     * @param PathServiceInterface $pathService
     * @param PdfFactoryInterface $pdfFactory
     * @param IssueReportServiceInterface $issueReportService
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(ImageServiceInterface $imageService, LayoutServiceInterface $layoutService, RegistryInterface $registry, TranslatorInterface $translator, PathServiceInterface $pathService, PdfFactoryInterface $pdfFactory, IssueReportServiceInterface $issueReportService, TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
    {
        $this->imageService = $imageService;
        $this->layoutService = $layoutService;
        $this->doctrine = $registry;
        $this->translator = $translator;
        $this->pathService = $pathService;
        $this->pdfFactory = $pdfFactory;
        $this->issueReportService = $issueReportService;
        $this->typographyService = $typographyService;
        $this->colorService = $colorService;
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

        // initialize pdf report
        $document = $this->createPdfDocument($constructionSite->getName(), $author);
        $layoutFactory = new LayoutFactory($document, $this->layoutService);
        $buildingBlocks = new PdfBuildingBlocks($this->typographyService, $this->colorService);

        // add introduction
        $introductionContent = $this->getIntroductionContent($constructionSite, $filter, $reportElements);
        $this->issueReportService->addIntroduction($layoutFactory, $buildingBlocks, $introductionContent);

        //add tables
        $aggregatedIssues = [];
        if ($reportElements->getTableByCraftsman()) {
            $aggregatedIssues[] = $this->aggregateIssuesByCraftsman($issues, $reportConfiguration);
        }
        if ($reportElements->getTableByMap()) {
            $aggregatedIssues[] = $this->aggregatedIssuesByMap($issues, $reportConfiguration);
        }
        if ($reportElements->getTableByTrade()) {
            $aggregatedIssues[] = $this->aggregatedIssuesByTrade($issues, $reportConfiguration);
        }
        foreach ($aggregatedIssues as $aggregatedIssue) {
            $this->issueReportService->addAggregatedIssueTable($layoutFactory, $buildingBlocks, $aggregatedIssue);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];

            $mapContent = $this->getMapContent($map, $issues, $reportElements, $reportConfiguration);
            $this->issueReportService->addMap($layoutFactory, $buildingBlocks, $mapContent);
        }

        $filePath = $this->getFilePath($constructionSite);
        $document->save($filePath);

        return $filePath;
    }

    /**
     * @param string $title
     * @param string $author
     *
     * @throws \Exception
     *
     * @return PdfDocumentInterface
     */
    private function createPdfDocument(string $title, string $author)
    {
        $footer = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $logoPath = $this->pathService->getAssetsRoot() . \DIRECTORY_SEPARATOR . 'report' . \DIRECTORY_SEPARATOR . 'logo.png';
        $pdfDocument = $this->pdfFactory->create($title, $footer, $logoPath);

        $pdfDocument->setMeta($title, $author);

        return $pdfDocument;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param ReportElements $reportElements
     *
     * @return IntroductionContent
     */
    private function getIntroductionContent(ConstructionSite $constructionSite, Filter $filter, ReportElements $reportElements)
    {
        $introductionContent = new IntroductionContent();
        $introductionContent->setConstructionSiteName($constructionSite->getName());
        $introductionContent->setConstructionSiteImage($this->imageService->getSizeForConstructionSite($constructionSite, ImageServiceInterface::SIZE_REPORT_ISSUE));
        $introductionContent->setConstructionSiteAddressLines($constructionSite->getAddressLines());
        $introductionContent->setFilterEntries($this->getFilterEntries($filter));
        $introductionContent->setReportElements($this->getReportElements($reportElements));

        return $introductionContent;
    }

    /**
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     *
     * @return AggregatedIssuesContent
     */
    private function aggregateIssuesByCraftsman(array $issues, ReportConfiguration $reportConfiguration)
    {
        $aggregatedIssues = new AggregatedIssuesContent();
        $aggregatedIssues->setTableDescription($this->translator->trans('table.by_craftsman', [], 'report'));

        /* @var Craftsman[] $orderedCraftsman */
        /* @var Issue[][] $issuesPerCraftsman */
        IssueHelper::issuesToOrderedCraftsman($issues, $orderedCraftsman, $issuesPerCraftsman);

        //prepare header & content with specific content
        $aggregatedIssues->setIdentifierHeader([$this->translator->trans('entity.name', [], 'entity_craftsman')]);

        //add name table
        foreach ($orderedCraftsman as $craftsmanId => $craftsman) {
            $aggregatedIssues->addIdentifierContent([$craftsman->getName()]);
        }

        $aggregatedIssues->setIssuesHeader($this->getAggregatedIssuesTableHeader($reportConfiguration));
        $aggregatedIssues->setIssuesContent($this->getAggregatedIssuesTableContent($orderedCraftsman, $issuesPerCraftsman, $reportConfiguration));

        return $aggregatedIssues;
    }

    /**
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     *
     * @return AggregatedIssuesContent
     */
    private function aggregatedIssuesByMap(array $issues, ReportConfiguration $reportConfiguration)
    {
        $aggregatedIssues = new AggregatedIssuesContent();
        $aggregatedIssues->setTableDescription($this->translator->trans('table.by_map', [], 'report'));

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //prepare header & content with specific content
        $aggregatedIssues->setIdentifierHeader([$this->translator->trans('context', [], 'entity_map'), $this->translator->trans('entity.name', [], 'entity_map')]);

        //add name table
        foreach ($orderedMaps as $mapId => $map) {
            $aggregatedIssues->addIdentifierContent([$map->getContext(), $map->getName()]);
        }

        $aggregatedIssues->setIssuesHeader($this->getAggregatedIssuesTableHeader($reportConfiguration));
        $aggregatedIssues->setIssuesContent($this->getAggregatedIssuesTableContent($orderedMaps, $issuesPerMap, $reportConfiguration));

        return $aggregatedIssues;
    }

    /**
     * @param Issue[] $issues
     * @param ReportConfiguration $reportConfiguration
     *
     * @return AggregatedIssuesContent
     */
    private function aggregatedIssuesByTrade(array $issues, ReportConfiguration $reportConfiguration)
    {
        $aggregatedIssues = new AggregatedIssuesContent();
        $aggregatedIssues->setTableDescription($this->translator->trans('table.by_trade', [], 'report'));

        /* @var string[] $orderedTrade */
        /* @var Issue[][] $issuesPerTrade */
        IssueHelper::issuesToOrderedTrade($issues, $orderedTrade, $issuesPerTrade);

        //prepare header & content with specific content
        $aggregatedIssues->setIdentifierHeader([$this->translator->trans('trade', [], 'entity_craftsman')]);

        //add name table
        foreach ($orderedTrade as $trade) {
            $aggregatedIssues->addIdentifierContent([$trade]);
        }

        $aggregatedIssues->setIssuesHeader($this->getAggregatedIssuesTableHeader($reportConfiguration));
        $aggregatedIssues->setIssuesContent($this->getAggregatedIssuesTableContent($orderedTrade, $issuesPerTrade, $reportConfiguration));

        return $aggregatedIssues;
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
        $countPerElements = [];
        foreach ($elements as $index => $element) {
            $countPerElement = [0, 0, 0];
            foreach ($issuesPerElement[$index] as $issue) {
                if ($issue->getStatusCode() >= Issue::REVIEW_STATUS) {
                    ++$countPerElement[2];
                } elseif ($issue->getStatusCode() >= Issue::RESPONSE_STATUS) {
                    ++$countPerElement[1];
                } else {
                    ++$countPerElement[0];
                }
            }
            $countPerElements[$index] = $countPerElement;
        }

        //add registration count if filter did not exclude
        if ($configuration->showRegistrationStatus()) {
            foreach ($countPerElements as $elementId => $count) {
                $tableContent[$elementId][] = $count[0];
            }
        }

        //add response count if filter did not exclude
        if ($configuration->showRespondedStatus()) {
            foreach ($countPerElements as $elementId => $count) {
                $tableContent[$elementId][] = $count[1];
            }
        }

        //add review count if filter did not exclude
        if ($configuration->showReviewedStatus()) {
            foreach ($countPerElements as $elementId => $count) {
                $tableContent[$elementId][] = $count[2];
            }
        }

        return $tableContent;
    }

    /**
     * @param Map $map
     * @param Issue[] $issues
     * @param ReportElements $elements
     * @param ReportConfiguration $reportConfiguration
     *
     * @return MapContent
     */
    private function getMapContent(Map $map, array $issues, ReportElements $elements, ReportConfiguration $reportConfiguration)
    {
        $mapContent = new MapContent();

        $mapContent->setMapName($map->getName());
        $mapContent->setMapContext($map->getContext());
        $mapContent->setMapImage($this->imageService->generateMapImageForReport($map, $issues, ImageServiceInterface::SIZE_REPORT_MAP));

        $mapContent->setIssuesTableHeader($this->getIssuesTableHeader($reportConfiguration));
        $mapContent->setIssuesTableContent($this->getIssuesTableContent($issues, $reportConfiguration));

        $mapContent->setMapImage($elements->getWithImages() ? $this->getIssueImages($issues) : []);

        return $mapContent;
    }

    /**
     * @param Issue[] $issues
     *
     * @return IssueImage[]
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

            $issueImage = new IssueImage();
            $issueImage->setImagePath($imagePath);
            $issueImage->setNumber($issue->getNumber());
            $issueImages[] = $currentIssue;
        }

        return $issueImages;
    }

    /**
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
     * @return string[]
     */
    private function getReportElements(ReportElements $reportElements): array
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

        return $elements;
    }
}
