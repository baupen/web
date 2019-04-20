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
use App\Service\Report\Legacy\PdfDefinition;
use App\Service\Report\Legacy\Report;
use App\Service\Report\ReportElements;
use function count;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     * @param ReportElements $elements
     *
     * @throws Exception
     *
     * @return string
     */
    public function generatePdfReport(ConstructionSite $constructionSite, Filter $filter, ?string $author, ReportElements $elements)
    {
        $issues = $this->doctrine->getRepository(Issue::class)->findByFilter($filter);

        //create folder
        $generationTargetFolder = $this->pathService->getTransientFolderForReports($constructionSite);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        //only generate report if it does not already exist
        $filePath = $generationTargetFolder . DIRECTORY_SEPARATOR . uniqid() . '.pdf';
        if (!file_exists($filePath) || $this->disableCache) {
            $formattedDate = (new DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT);
            if ($author === null) {
                $footer = $this->translator->trans('generated', ['%date%' => $formattedDate], 'report');
            } else {
                $footer = $this->translator->trans('generated_with_author', ['%date%' => $formattedDate, '%name%' => $author], 'report');
            }
            $this->render($constructionSite, $filter, $footer, $elements, $issues, $filePath);
        }

        return $filePath;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     * @param ReportElements $reportElements
     * @param Issue[] $issues
     * @param string $filePath
     */
    private function render(ConstructionSite $constructionSite, Filter $filter, string $author, ReportElements $reportElements, array $issues, string $filePath)
    {
        // initialize report
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $author, __DIR__ . '/../../assets/report/logo.png');
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
            //add table with issues
            //add columns with images
        }

        $report->save($filePath);
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
        $filterEntries = [];

        /*
         * intentionally ignoring isMarked as this is part of the application, not the report
         */

        //collect all set status
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
            if (\count($status) === 4) {
                $allStatus = $this->translator->trans('status_values.all', [], 'entity_issue');
                $filterEntries[$key] = $allStatus;
            } else {
                $or = $this->translator->trans('introduction.filter.or', [], 'report');
                $filterEntries[$key] = implode(' ' . $or . ' ', $status);
            }
        }

        //add craftsmen
        if ($filter->getCraftsmen() !== null) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmen()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName();
            }
            $filterEntries[$this->translator->trans('introduction.filter.craftsmen', ['%count%' => \count($names)], 'report')] = implode(', ', $names);
        }

        //add maps
        if ($filter->getMaps() !== null) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMaps()]);
            $names = [];
            foreach ($entities as $item) {
                $names[] = $item->getName() . ' (' . $item->getContext() . ')';
            }
            $filterEntries[$this->translator->trans('introduction.filter.maps', ['%count%' => \count($names)], 'report')] = implode(', ', $names);
        }

        //add limit
        $limitValue = $this->getDateTimeString($filter->getLimitStart(), $filter->getLimitEnd());
        if ($limitValue !== '') {
            $filterEntries[$this->translator->trans('response_limit', [], 'entity_issue')] = $limitValue;
        }

        //set other properties
        if ($filter->getTrades() !== null) {
            $names = [];
            foreach ($filter->getTrades() as $trade) {
                $names[] = $trade;
            }
            $filterEntries[$this->translator->trans('introduction.filter.trades', ['%count%' => \count($names)], 'report')] = implode(', ', $names);
        }

        // collect set time
        $timeEntries = [];
        if ($filter->getRegistrationStatus() !== null) {
            $trans = $this->translator->trans('status_values.registered', [], 'entity_issue');
            $range = $this->getDateTimeString($filter->getRegistrationStart(), $filter->getRegistrationEnd(), $trans);
            if ($range !== '') {
                $timeEntries[] = $range;
            }
        }
        if ($filter->getRespondedStatus() !== null) {
            $trans = $this->translator->trans('status_values.responded', [], 'entity_issue');
            $range = $this->getDateTimeString($filter->getRespondedStart(), $filter->getRespondedEnd(), $trans);
            if ($range !== '') {
                $timeEntries[] = $range;
            }
        }
        if ($filter->getReviewedStatus() !== null) {
            $trans = $this->translator->trans('status_values.reviewed', [], 'entity_issue');
            $range = $this->getDateTimeString($filter->getReviewedStart(), $filter->getRespondedEnd(), $trans);
            if ($range !== '') {
                $timeEntries[] = $range;
            }
        }

        if (\count($timeEntries) > 0) {
            //convert all set time status to a single string
            $and = $this->translator->trans('introduction.filter.and', [], 'report');
            $statusEntry = implode(' ' . $and . ' ', $timeEntries);
            $filterEntries[$this->translator->trans('introduction.filter.time', [], 'report')] = $statusEntry;
        }

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
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @param string|null $prefix
     *
     * @return string
     */
    private function getDateTimeString($start, $end, string $prefix = null): string
    {
        if ($start !== null) {
            if ($end !== null) {
                $rangeString = $start->format(DateTimeFormatter::DATE_TIME_FORMAT) . ' - ' . $end->format(DateTimeFormatter::DATE_TIME_FORMAT);
            } else {
                $rangeString = $this->translator->trans('introduction.filter.later_than', ['%date%' => $start->format(DateTimeFormatter::DATE_TIME_FORMAT)], 'report');
            }
        } elseif ($end !== null) {
            $rangeString = $this->translator->trans('introduction.filter.earlier_than', ['%date%' => $end->format(DateTimeFormatter::DATE_TIME_FORMAT)], 'report');
        } else {
            return '';
        }

        if ($prefix === null) {
            return $rangeString;
        }

        return $prefix . ' (' . $rangeString . ')';
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

        if ($showRegistered) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.registered', [], 'entity_issue')], 'report');
        }

        if ($showResponded) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.responded', [], 'entity_issue')], 'report');
        }

        if ($showReviewed) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('status_values.reviewed', [], 'entity_issue')], 'report');
        }

        $tableContent = [];
        foreach ($issues as $issue) {
            $row = [];
            $row[] = $issue->getNumber();
            $row[] = $issue->getDescription();
            $row[] = ($issue->getResponseLimit() !== null) ? $issue->getResponseLimit()->format(DateTimeFormatter::DATE_FORMAT) : '';

            if ($showRegistered) {
                $row[] = $issue->getRegisteredAt() !== null ? $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getRegistrationBy()->getName() : '';
            }

            if ($showResponded) {
                $row[] = $issue->getRespondedAt() !== null ? $issue->getRespondedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getResponseBy()->getName() : '';
            }

            if ($showReviewed) {
                $row[] = $issue->getReviewedAt() !== null ? $issue->getReviewedAt()->format(DateTimeFormatter::DATE_FORMAT) . "\n" . $issue->getReviewBy()->getName() : '';
            }

            $tableContent[] = $row;
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
}
