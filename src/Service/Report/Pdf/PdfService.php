<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\DateTimeFormatter;
use App\Helper\FileHelper;
use App\Helper\IssueHelper;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfService
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
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $reportAssetDir;

    /**
     * ReportService constructor.
     */
    public function __construct(ImageServiceInterface $imageService, TranslatorInterface $translator, PathServiceInterface $pathService, ManagerRegistry $doctrine, string $reportAssetDir)
    {
        $this->imageService = $imageService;
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->pathService = $pathService;
        $this->reportAssetDir = $reportAssetDir;
    }

    public function generatePdfReport(array $issues, Filter $filter, ReportElements $reportElements, ?string $author = null): string
    {
        $constructionSite = $filter->getConstructionSite();

        $this->setScriptRuntime(count($issues));

        $formattedDate = (new DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT);
        if (null === $author) {
            $footer = $this->translator->trans('generated', ['%date%' => $formattedDate], 'report');
        } else {
            $footer = $this->translator->trans('generated_with_author', ['%date%' => $formattedDate, '%name%' => $author], 'report');
        }

        // initialize report
        $logoPath = $this->reportAssetDir.'/logo.png';
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $footer, $logoPath);
        $report = new Report($pdfDefinition, $this->reportAssetDir);

        $this->addIntroduction($report, $constructionSite, $issues, $filter, $reportElements);

        if (count($issues) > 0) {
            $this->addIssueContent($filter, $reportElements, $issues, $report);
        }

        $folder = $this->pathService->getTransientFolderForReports();
        FileHelper::ensureFolderExists($folder);

        $sanitizedConstructionSiteName = FileHelper::sanitizeFileName($constructionSite->getName());
        $humanReadablePrefix = (new DateTime())->format(DateTimeFormatter::FILESYSTEM_DATE_TIME_FORMAT).'_'.$sanitizedConstructionSiteName;
        $optimalFilename = $humanReadablePrefix.'.pdf';
        $filename = file_exists($optimalFilename) ? $humanReadablePrefix.'_'.uniqid().'.pdf' : $optimalFilename;

        $path = $folder.'/'.$filename;
        $report->save($path);

        return $filename;
    }

    private function addIssueContent(Filter $filter, ReportElements $reportElements, array $issues, Report $report): void
    {
        // add tables
        if ($reportElements->getTableByCraftsman()) {
            $this->addTableByCraftsman($report, $issues);
        }
        if ($reportElements->getTableByMap()) {
            $this->addTableByMap($report, $issues);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];
            $this->addMap($report, $map, $issues, $reportElements->getWithRenders());
            $this->addIssueTable($report, $filter, $issues);
            if ($reportElements->getWithImages()) {
                $this->addIssueImageGrid($report, $issues);
            }
        }
    }

    /**
     * @param Issue[] $issues
     */
    private function addMap(Report $report, Map $map, array $issues, bool $showMap)
    {
        $path = $map->getFile() && $showMap ? $this->imageService->renderMapFileWithIssuesToJpg($map->getFile(), $issues, ImageServiceInterface::SIZE_FULL) : null;

        $report->addMap($map->getName(), $map->getContext(), $path);
    }

    /**
     * @param Issue[] $issues
     */
    private function addIssueImageGrid(Report $report, array $issues)
    {
        $columnCount = 4;

        $imageGrid = [];
        $currentRow = [];
        foreach ($issues as $issue) {
            $currentIssue = [];

            if (!$issue->getImage()) {
                continue;
            }

            $imagePath = $this->imageService->resizeIssueImage($issue->getImage(), ImageServiceInterface::SIZE_PREVIEW);
            if (null === $imagePath) {
                continue;
            }

            $currentIssue['imagePath'] = $imagePath;
            $currentIssue['identification'] = $issue->getNumber();
            $currentRow[] = $currentIssue;

            // add row to grid if applicable
            if (count($currentRow) === $columnCount) {
                $imageGrid[] = $currentRow;
                $currentRow = [];
            }
        }
        if (count($currentRow) > 0) {
            $imageGrid[] = $currentRow;
        }

        $report->addImageGrid($imageGrid, $columnCount);
    }

    private function addIntroduction(Report $report, ConstructionSite $constructionSite, array $issues, Filter $filter, ReportElements $reportElements)
    {
        $filterEntries = [];

        /*
         * intentionally ignoring isMarked as this is part of the application, not the report
         */

        if (null !== $filter->getWasAddedWithClient()) {
            $key = $this->translator->trans('was_added_with_client', [], 'entity_issue');

            $filterEntries[$key] = true === $filter->getWasAddedWithClient() ?
                $this->translator->trans('yes', [], 'enum_boolean_type') :
                $this->translator->trans('no', [], 'enum_boolean_type');
        }

        if (null !== $filter->getNumbers()) {
            $key = $this->translator->trans('number', [], 'entity_issue');
            $or = $this->translator->trans('introduction.filter.or', [], 'report');
            $filterEntries[$key] = implode(' '.$or.' ', $filter->getNumbers());
        }

        if (null !== $filter->getDescription()) {
            $key = $this->translator->trans('description', [], 'entity_issue');
            $filterEntries[$key] = $filter->getDescription();
        }

        // collect all set status
        if (null !== $filter->getState()) {
            $status = [];
            if ($filter->getState() & Issue::STATE_CREATED) {
                $status[] = $this->translator->trans('state_values.created', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_REGISTERED) {
                $status[] = $this->translator->trans('state_values.registered', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_RESOLVED) {
                $status[] = $this->translator->trans('state_values.resolved', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_CLOSED) {
                $status[] = $this->translator->trans('state_values.closed', [], 'entity_issue');
            }

            $key = $this->translator->trans('status', [], 'entity_issue');
            $or = $this->translator->trans('introduction.filter.or', [], 'report');
            $filterEntries[$key] = implode(' '.$or.' ', $status);
        }

        // add craftsmen
        if (null !== $filter->getCraftsmanIds()) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmanIds()]);
            $names = array_map(function (Craftsman $craftsman) {
                return $craftsman->getName();
            }, $entities);
            $craftsmen = $this->translator->trans('introduction.filter.craftsmen', ['%count%' => count($names)], 'report');
            $filterEntries[$craftsmen] = implode(', ', $names);
        }

        // add maps
        if (null !== $filter->getMapIds()) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMapIds()]);
            $names = array_map(function (Map $map) {
                return $map->getNameWithContext();
            }, $entities);
            $maps = $this->translator->trans('introduction.filter.maps', ['%count%' => count($names)], 'report');
            $filterEntries[$maps] = implode(', ', $names);
        }

        $deadline = $this->translator->trans('deadline', [], 'entity_issue');
        $filterEntries[$deadline] = $this->tryGetDateString($filter->getDeadlineBefore(), $filter->getDeadlineAfter());

        $createdAt = $this->translator->trans('created_at', [], 'trait_issue_status');
        $filterEntries[$createdAt] = $this->tryGetDateString($filter->getCreatedAtBefore(), $filter->getCreatedAtAfter());

        $registeredAt = $this->translator->trans('registered_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getRegisteredAtBefore(), $filter->getRegisteredAtAfter());

        $resolvedAt = $this->translator->trans('resolved_at', [], 'trait_issue_status');
        $filterEntries[$resolvedAt] = $this->tryGetDateString($filter->getResolvedAtBefore(), $filter->getResolvedAtAfter());

        $closedAt = $this->translator->trans('closed_at', [], 'trait_issue_status');
        $filterEntries[$closedAt] = $this->tryGetDateString($filter->getClosedAtBefore(), $filter->getClosedAtAfter());

        // clear empty entries
        $filterEntries = array_filter($filterEntries);

        // add list of elements which are part of this report
        $elements = [];
        if ($reportElements->getTableByCraftsman()) {
            $elements[] = $this->translator->trans('table.by_craftsman', [], 'report');
        }
        if ($reportElements->getTableByMap()) {
            $elements[] = $this->translator->trans('table.by_map', [], 'report');
        }
        $elements[] = $this->translator->trans('issues.detailed', [], 'report');
        if ($reportElements->getWithImages()) {
            $elements[count($elements) - 1] .= ' '.$this->translator->trans('issues.with_images', [], 'report');
        }
        if ($reportElements->getWithRenders()) {
            $elements[count($elements) - 1] .= ' '.$this->translator->trans('issues.with_renders', [], 'report');
        }
        $reportElements = implode(', ', $elements);

        $addressLines = implode("\n", $constructionSite->getAddressLines());

        $constructionSiteImage = $constructionSite->getImage() ? $this->imageService->resizeConstructionSiteImage($constructionSite->getImage(), ImageServiceInterface::SIZE_PREVIEW) : null;

        $report->addIntroduction(
            $constructionSiteImage,
            $constructionSite->getName(),
            $addressLines,
            $reportElements,
            $filterEntries,
            $this->translator->trans('entity.name', [], 'entity_filter')
        );
    }

    private function tryGetDateString(?DateTime $before, ?DateTime $after): ?string
    {
        $beforeString = null !== $before ? $before->format(DateTimeFormatter::DATE_FORMAT) : null;
        $afterString = null !== $after ? $after->format(DateTimeFormatter::DATE_FORMAT) : null;

        if (null !== $before && null !== $after) {
            return $afterString.' - '.$beforeString;
        }

        if (null !== $before) {
            return $this->translator->trans('introduction.filter.earlier_than', ['%date%' => $beforeString], 'report');
        }

        if (null !== $after) {
            return $this->translator->trans('introduction.filter.later_than', ['%date%' => $afterString], 'report');
        }

        return null;
    }

    /**
     * @param Issue[] $issues
     */
    private function addIssueTable(Report $report, Filter $filter, array $issues)
    {
        $showRegistered = null === $filter->getRegisteredAtBefore() || $filter->getRegisteredAtAfter();
        $showResolved = null === $filter->getResolvedAtBefore() || $filter->getResolvedAtAfter();
        $showClosed = null === $filter->getClosedAtBefore() || $filter->getClosedAtAfter();

        $formatDateTime = function (?DateTime $dateTime, string $format) {
            return $dateTime ? $dateTime->format($format) : '-';
        };

        $tableContent = [];
        $deadlineFormatInversion = [];
        $maxIssueNumber = 0;
        foreach ($issues as $issue) {
            $row = [];

            $row[] = $issue->getNumber();
            $maxIssueNumber = max($issue->getNumber(), $maxIssueNumber);

            $row[] = $issue->getCraftsman()->getCompany()."\n".$issue->getCraftsman()->getTrade();
            $row[] = $issue->getDescription();

            $deadlineFormat = $formatDateTime($issue->getDeadline(), DateTimeFormatter::DATE_FORMAT);
            $row[] = $deadlineFormat;
            $deadlineFormatInversion[$deadlineFormat] = $formatDateTime($issue->getDeadline(), DateTimeFormatter::ISO_DATE_FORMAT);

            if ($showRegistered) {
                $row[] = $formatDateTime($issue->getRegisteredAt(), DateTimeFormatter::DATE_FORMAT);
            }

            if ($showResolved) {
                $row[] = $formatDateTime($issue->getResolvedAt(), DateTimeFormatter::DATE_FORMAT);
            }

            if ($showClosed) {
                $row[] = $formatDateTime($issue->getClosedAt(), DateTimeFormatter::DATE_FORMAT);
            }

            $tableContent[] = $row;
        }

        usort($tableContent, function ($a, $b) use ($deadlineFormatInversion) {
            // order by craftsman
            if ($a[1] !== $b[1]) {
                return strcmp($a[1], $b[1]);
            }

            // order by limit (early first)
            if ($a[3] !== $b[3]) {
                return strcmp($deadlineFormatInversion[$a[3]], $deadlineFormatInversion[$b[3]]);
            }

            // order by number if rest is equal
            return strcmp($a[0], $b[0]);
        });

        $totalWidth = 190; // out of the pdfSize config model
        $cellPadding = 1.6 * 2; // out of the pdfSize config model
        $dateWidth = 19; // any smaller, the "abgeschlossen" in DE introduces a line break
        $numberWidth = 1.8; // seems alright even with 5 digits

        $tableSizes = [];

        $tableHeader[] = '#';
        $tableSizes[] = $cellPadding + strlen($maxIssueNumber) * $numberWidth;

        $tableHeader[] = $this->translator->trans('entity.name', [], 'entity_craftsman');
        $tableSizes[] = 0;

        $tableHeader[] = $this->translator->trans('description', [], 'entity_issue');
        $tableSizes[] = 0;

        $tableHeader[] = $this->translator->trans('deadline', [], 'entity_issue');
        $tableSizes[] = $dateWidth;

        if ($showRegistered) {
            $tableHeader[] = $this->translator->trans('state_values.registered', [], 'entity_issue');
            $tableSizes[] = $dateWidth;
        }

        if ($showResolved) {
            $tableHeader[] = $this->translator->trans('state_values.resolved', [], 'entity_issue');
            $tableSizes[] = $dateWidth;
        }

        if ($showClosed) {
            $tableHeader[] = $this->translator->trans('state_values.closed', [], 'entity_issue');
            $tableSizes[] = $dateWidth;
        }

        $availableWidth = $totalWidth - array_sum($tableSizes);
        $tableSizes[1] = $availableWidth * 0.4;
        $tableSizes[2] = $availableWidth * 0.6;

        $report->addSizedTable($tableSizes, $tableHeader, $tableContent);
    }

    /**
     * @param Issue[][] $issuesPerMap
     */
    private function addAggregatedIssuesInfo(array $orderedMaps, array $issuesPerMap, array &$tableContent, array &$tableHeader)
    {
        // count issue status per map
        $countsPerElement = [];
        foreach ($orderedMaps as $index => $element) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerMap[$index] as $issue) {
                if (null === $issue->getClosedAt() && null === $issue->getResolvedAt()) {
                    ++$countPerMap[0];
                }

                if ($issue->getResolvedAt()) {
                    ++$countPerMap[1];
                }

                if ($issue->getClosedAt()) {
                    ++$countPerMap[2];
                }
            }
            $countsPerElement[$index] = $countPerMap;
        }

        $tableHeader[] = $this->translator->trans('state_values.registered', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[0];
        }

        $tableHeader[] = $this->translator->trans('state_values.resolved', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[1];
        }

        $tableHeader[] = $this->translator->trans('state_values.closed', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[2];
        }
    }

    private function getAggregatedIssuesTotal(array $tableContent)
    {
        $total = $this->translator->trans('table.total', [], 'report');
        $totalRow = [$total, 0, 0, 0];
        foreach ($tableContent as $entry) {
            $totalRow[1] += $entry[1];
            $totalRow[2] += $entry[2];
            $totalRow[3] += $entry[3];
        }

        return $totalRow;
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByMap(Report $report, array $issues)
    {
        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        // prepare header & content with specific content
        $tableHeader = [$this->translator->trans('entity.name', [], 'entity_map')];

        // add map name & map context to table
        $tableContent = [];
        foreach ($orderedMaps as $mapId => $map) {
            $tableContent[$mapId] = [$map->getNameWithContext()];
        }

        // add accumulated info
        $this->addAggregatedIssuesInfo($orderedMaps, $issuesPerMap, $tableContent, $tableHeader);
        $tableFooter = $this->getAggregatedIssuesTotal($tableContent);

        // write to pdf
        $report->addTable($tableHeader, $tableContent, $tableFooter, $this->translator->trans('table.by_map', [], 'report'), 100);
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByCraftsman(Report $report, array $issues)
    {
        /* @var Craftsman[] $orderedCraftsman */
        /* @var Issue[][] $issuesPerCraftsman */
        IssueHelper::issuesToOrderedCraftsman($issues, $orderedCraftsman, $issuesPerCraftsman);

        // prepare header & content with specific content
        $tableHeader = [$this->translator->trans('entity.name', [], 'entity_craftsman')];

        // add map name & map context to table
        $tableContent = [];
        foreach ($orderedCraftsman as $craftsmanId => $craftsman) {
            $tableContent[$craftsmanId] = [$craftsman->getName()];
        }

        // add accumulated info
        $this->addAggregatedIssuesInfo($orderedCraftsman, $issuesPerCraftsman, $tableContent, $tableHeader);
        $tableFooter = $this->getAggregatedIssuesTotal($tableContent);

        // write to pdf
        $report->addTable($tableHeader, $tableContent, $tableFooter, $this->translator->trans('table.by_craftsman', [], 'report'), 100);
    }

    private function setScriptRuntime(int $numberOfIssues): void
    {
        // 0.5s per issue seems reasonable
        $maxExecutionTime = max(120, $numberOfIssues / 2);
        $executionTime = max(ini_get('max_execution_time'), $maxExecutionTime);
        ini_set('max_execution_time', $executionTime);

        // 500 kb per issue seems reasonable
        $memoryLimitMbs = max(256, 0.5 * $numberOfIssues);
        ini_set('memory_limit', $memoryLimitMbs.'M');
    }
}
