<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport;

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
use App\Service\Report\IssueReport\Interfaces\PrintFactoryInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\IssueImage;
use App\Service\Report\IssueReport\Model\MapContent;
use App\Service\Report\IssueReport\Model\MetaData;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\ReportConfiguration;
use App\Service\Report\ReportElements;
use function count;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Exception;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewReportService
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
     * @var ManagerRegistry
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
     */
    public function __construct(ImageServiceInterface $imageService, LayoutServiceInterface $layoutService, ManagerRegistry $registry, TranslatorInterface $translator, PathServiceInterface $pathService, PdfFactoryInterface $pdfFactory, IssueReportServiceInterface $issueReportService, TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
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
     * @param string $author
     *
     * @throws Exception
     *
     * @return string
     */
    public function generatePdfReport(ConstructionSite $constructionSite, Filter $filter, ?string $author, ReportElements $reportElements)
    {
        // initialize pdf report
        $printFactory = new PrintFactory($this->typographyService, $this->colorService, $this->layoutService);

        $pageLayoutContent = $this->getMetaData($constructionSite, $author);
        $layout = $printFactory->getLayout($pageLayoutContent);
        $document = $this->createPdfDocument($layout);

        $layoutFactory = new LayoutFactory($document, $this->layoutService);
        $this->addReportElements($layoutFactory, $printFactory, $constructionSite, $filter, $reportElements);

        // persist
        $filePath = $this->getFilePath($constructionSite);
        $document->save($filePath);

        return $filePath;
    }

    private function addReportElements(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $buildingBlocks, ConstructionSite $constructionSite, Filter $filter, ReportElements $reportElements)
    {
        $issues = $this->doctrine->getRepository(Issue::class)->findByFilter($filter);
        $reportConfiguration = new ReportConfiguration($filter);

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
    }

    /**
     * @return PdfDocumentInterface
     */
    private function createPdfDocument(PdfPageLayoutInterface $pageLayout)
    {
        $fontPath = $this->pathService->getAssetsRoot().DIRECTORY_SEPARATOR.'report'.DIRECTORY_SEPARATOR.'fonts';
        $defaultFontFamily = $this->typographyService->getFontFamily();
        $this->pdfFactory->configure(['tcpdf' => ['font_path' => $fontPath, 'default_font_family' => $defaultFontFamily]]);

        return $this->pdfFactory->create($pageLayout);
    }

    /**
     * @param string $author
     *
     * @throws Exception
     *
     * @return MetaData
     */
    private function getMetaData(ConstructionSite $constructionSite, ?string $author)
    {
        $pageLayoutContent = new MetaData();

        $pageLayoutContent->setTitle($constructionSite->getName());
        $pageLayoutContent->setAuthor($author);

        $formattedDateTime = (new DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT);
        if (null === $author) {
            $generationInfoText = $this->translator->trans('generated', ['%date%' => $formattedDateTime], 'report');
        } else {
            $generationInfoText = $this->translator->trans('generated_with_author', ['%date%' => $formattedDateTime, '%name%' => $author], 'report');
        }
        $pageLayoutContent->setGenerationInfoText($generationInfoText);

        $logoPath = $this->pathService->getAssetsRoot().DIRECTORY_SEPARATOR.'report'.DIRECTORY_SEPARATOR.'logo.png';
        $pageLayoutContent->setLogoPath($logoPath);

        return $pageLayoutContent;
    }

    /**
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
     * @param Issue[][] $issuesPerElement
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

        return array_values($tableContent);
    }

    /**
     * @param Issue[] $issues
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

        if ($elements->getWithImages()) {
            foreach ($this->getIssueImages($issues) as $issueImage) {
                $mapContent->addIssueImage($issueImage);
            }
        }

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
            $imagePath = $this->imageService->getSizeForIssue($issue, ImageServiceInterface::SIZE_REPORT_ISSUE);
            if (null === $imagePath) {
                continue;
            }

            $issueImage = new IssueImage();
            $issueImage->setImagePath($imagePath);
            $issueImage->setNumber($issue->getNumber());
            $issueImages[] = $issueImage;
        }

        return $issueImages;
    }

    /**
     * @param Issue[] $issues
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
            $row[] = (null !== $issue->getResponseLimit()) ? $issue->getResponseLimit()->format(DateTimeFormatter::DATE_FORMAT) : '';

            if ($reportConfiguration->showRegistrationStatus()) {
                $row[] = null === $issue->getRegisteredAt() ? '' : $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getRegistrationBy()->getName();
            }

            if ($reportConfiguration->showRespondedStatus()) {
                $row[] = null === $issue->getRespondedAt() ? '' : $issue->getRespondedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getResponseBy()->getName();
            }

            if ($reportConfiguration->showReviewedStatus()) {
                $row[] = null === $issue->getReviewedAt() ? '' : $issue->getReviewedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getReviewBy()->getName();
            }

            $tableContent[] = $row;
        }

        return $tableContent;
    }

    /**
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
     * @throws Exception
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

        $date = (new DateTime())->format('Y-m-dTH_i');

        return $generationTargetFolder.DIRECTORY_SEPARATOR.$date.'_'.uniqid().'.pdf';
    }

    private function getFilterEntries(Filter $filter): array
    {
        $filterEntries = [];

        //add anyStatus
        if ($filter->getAnyStatus() > 0) {
            $status = [];
            if ($filter->getAnyStatus() & Filter::STATUS_REGISTERED) {
                $status[] = $this->translator->trans('status_values.registered', [], 'entity_issue');
            }
            if ($filter->getAnyStatus() & Filter::STATUS_READ) {
                $status[] = $this->translator->trans('status_values.read', [], 'entity_issue');
            }
            if ($filter->getAnyStatus() & Filter::STATUS_RESPONDED) {
                $status[] = $this->translator->trans('status_values.responded', [], 'entity_issue');
            }
            if ($filter->getAnyStatus() & Filter::STATUS_REVIEWED) {
                $status[] = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            }

            $key = $this->translator->trans('status', [], 'entity_issue');
            if (4 === \count($status)) {
                $allStatus = $this->translator->trans('status_values.all', [], 'entity_issue');
                $filterEntries[$key] = $allStatus;
            } else {
                $or = $this->translator->trans('introduction.filter.or', [], 'report');
                $filterEntries[$key] = implode(' '.$or.' ', $status);
            }
        }

        //add craftsmen
        if (null !== $filter->getCraftsmen()) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmen()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName();
            }

            $label = $this->translator->trans('introduction.filter.craftsmen', ['%count%' => \count($names)], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        //add maps
        if (null !== $filter->getMaps()) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMaps()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName().' ('.$item->getContext().')';
            }

            $label = $this->translator->trans('introduction.filter.maps', ['%count%' => \count($names)], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        //add limit
        if (null !== $filter->getLimitStart() || null !== $filter->getLimitEnd()) {
            $limitValue = $this->dateTimeRangeToText($filter->getLimitStart(), $filter->getLimitEnd());
            $label = $this->translator->trans('response_limit', [], 'entity_issue');
            $filterEntries[$label] = $limitValue;
        }

        //add trades
        if (null !== $filter->getTrades()) {
            $names = [];
            foreach ($filter->getTrades() as $item) {
                $names[] = $item;
            }

            $label = $this->translator->trans('introduction.filter.trades', ['%count%' => \count($names)], 'report');
            $filterEntries[$label] = implode(', ', $names);
        }

        // collect set time
        $timeEntries = [];
        if (null !== $filter->getRegistrationStatus()) {
            $trans = $this->translator->trans('status_values.registered', [], 'entity_issue');
            $range = $this->dateTimeRangeToText($filter->getRegistrationStart(), $filter->getRegistrationEnd(), $trans);
            if ('' !== $range) {
                $timeEntries[] = $range;
            }
        }
        if (null !== $filter->getRespondedStatus()) {
            $trans = $this->translator->trans('status_values.responded', [], 'entity_issue');
            $range = $this->dateTimeRangeToText($filter->getRespondedStart(), $filter->getRespondedEnd(), $trans);
            if ('' !== $range) {
                $timeEntries[] = $range;
            }
        }
        if (null !== $filter->getReviewedStatus()) {
            $trans = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            $range = $this->dateTimeRangeToText($filter->getReviewedStart(), $filter->getRespondedEnd(), $trans);
            if ('' !== $range) {
                $timeEntries[] = $range;
            }
        }

        if (\count($timeEntries) > 0) {
            //convert all set time status to a single string
            $and = $this->translator->trans('introduction.filter.and', [], 'report');
            $statusEntry = implode(' '.$and.' ', $timeEntries);
            $filterEntries[$this->translator->trans('introduction.filter.time', [], 'report')] = $statusEntry;
        }

        return $filterEntries;
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     */
    private function dateTimeRangeToText($start, $end, string $prefix = null): string
    {
        if (null !== $start) {
            if (null !== $end) {
                $rangeString = $start->format(DateTimeFormatter::DATE_FORMAT).' - '.$end->format(DateTimeFormatter::DATE_FORMAT);
            } else {
                $rangeString = $this->translator->trans('introduction.filter.later_than', ['%date%' => $start->format(DateTimeFormatter::DATE_FORMAT)], 'report');
            }
        } elseif (null !== $end) {
            $rangeString = $this->translator->trans('introduction.filter.earlier_than', ['%date%' => $end->format(DateTimeFormatter::DATE_FORMAT)], 'report');
        } else {
            return '';
        }

        if (null === $prefix) {
            return $rangeString;
        }

        return $prefix.' ('.$rangeString.')';
    }

    /**
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
            $issueDetailsLabel .= ' '.$this->translator->trans('issues.with_images', [], 'report');
        }
        $elements[] = $issueDetailsLabel;

        return $elements;
    }
}
