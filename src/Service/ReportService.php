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

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
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
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\PdfDefinition;
use App\Service\Report\Report;
use App\Service\Report\ReportElements;
use function count;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
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
     * @var ManagerRegistry
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
     * @var string
     */
    private $reportAssetDir;

    /**
     * ReportService constructor.
     */
    public function __construct(ImageServiceInterface $imageService, SerializerInterface $serializer, TranslatorInterface $translator, PathServiceInterface $pathService, string $reportAssetDir)
    {
        $this->imageService = $imageService;
        $this->serializer = $serializer;
        $this->translator = $translator;
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

        $path = $folder.'/'.(new DateTime())->format(DateTimeFormatter::FILESYSTEM_DATE_TIME_FORMAT).'_'.uniqid().'.pdf';
        $report->save($path);

        return $path;
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
            $this->addMap($report, $map, $issues);
            $this->addIssueTable($report, $filter, $issues);
            if ($reportElements->getWithImages()) {
                $this->addIssueImageGrid($report, $issues);
            }
        }
    }

    /**
     * @param Issue[] $issues
     */
    private function addMap(Report $report, Map $map, array $issues)
    {
        $path = $this->imageService->renderMapFileWithIssuesToJpg($map->getFile(), $issues, ImageServiceInterface::SIZE_FULL);

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

            //add row to grid if applicable
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

        //collect all set status
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
            if (4 === count($status)) {
                $allStatus = $this->translator->trans('state_values.all', [], 'entity_issue');
                $filterEntries[$key] = $allStatus;
            } else {
                $or = $this->translator->trans('introduction.filter.or', [], 'report');
                $filterEntries[$key] = implode(' '.$or.' ', $status);
            }
        }

        //add craftsmen
        if (null !== $filter->getCraftsmanIds()) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmanIds()]);
            $names = array_map(function (Craftsman $craftsman) {
                $craftsman->getName();
            }, $entities);
            $craftsmen = $this->translator->trans('introduction.filter.craftsmen', ['%count%' => count($names)], 'report');
            $filterEntries[$craftsmen] = implode(', ', $names);
        }

        //add trades
        if (null !== $filter->getCraftsmanTrades()) {
            $trades = $this->translator->trans('introduction.filter.trades', ['%count%' => count($filter->getCraftsmanTrades())], 'report');
            $filterEntries[$trades] = implode(', ', $filter->getCraftsmanTrades());
        }

        //add maps
        if (null !== $filter->getMapIds()) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMapIds()]);
            $names = array_map(function (Map $map) {
                $map->getContext();
            }, $entities);
            $maps = $this->translator->trans('introduction.filter.maps', ['%count%' => count($names)], 'report');
            $filterEntries[$maps] = implode(', ', $names);
        }

        $deadline = $this->translator->trans('deadline', [], 'entity_issue');
        $filterEntries[$deadline] = $this->tryGetDateString($filter->getDeadlineAtBefore(), $filter->getDeadlineAtAfter());

        $registeredAt = $this->translator->trans('registered_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getRegisteredAtBefore(), $filter->getRegisteredAtAfter());

        $registeredAt = $this->translator->trans('resolved_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getResolvedAtBefore(), $filter->getResolvedAtAfter());

        $registeredAt = $this->translator->trans('closed_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getClosedAtBefore(), $filter->getClosedAtAfter());

        // clear empty entries
        $filterEntries = array_filter($filterEntries);

        //add list of elements which are part of this report
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
        $reportElements = implode(', ', $elements);

        $addressLines = implode("\n", $constructionSite->getAddressLines());

        $report->addIntroduction(
            $this->imageService->resizeConstructionSiteImage($constructionSite->getImage(), ImageServiceInterface::SIZE_PREVIEW),
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

        $tableHeader[] = '#';
        $tableHeader[] = $this->translator->trans('entity.name', [], 'entity_craftsman');
        $tableHeader[] = $this->translator->trans('description', [], 'entity_issue');
        $tableHeader[] = $this->translator->trans('deadline', [], 'entity_issue');

        if ($showRegistered) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.registered', [], 'entity_issue')], 'report');
        }

        if ($showResolved) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.resolved', [], 'entity_issue')], 'report');
        }

        if ($showClosed) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.closed', [], 'entity_issue')], 'report');
        }

        $tableContent = [];
        foreach ($issues as $issue) {
            $row = [];
            $row[] = $issue->getNumber();
            $row[] = $issue->getCraftsman()->getCompany()."\n".$issue->getCraftsman()->getTrade();
            $row[] = $issue->getDescription();
            $row[] = (null !== $issue->getDeadline()) ? $issue->getDeadline()->format(DateTimeFormatter::DATE_FORMAT) : '';

            if ($showRegistered) {
                $row[] = null !== $issue->getRegisteredAt() ? $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getRegisteredBy()->getName() : '';
            }

            if ($showResolved) {
                $row[] = null !== $issue->getResolvedAt() ? $issue->getResolvedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getResolvedBy()->getName() : '';
            }

            if ($showClosed) {
                $row[] = null !== $issue->getClosedAt() ? $issue->getClosedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getClosedBy()->getName() : '';
            }

            $tableContent[] = $row;
        }

        $report->addTable($tableHeader, $tableContent, null, 10);
    }

    /**
     * @param Issue[][] $issuesPerMap
     */
    private function addAggregatedIssuesInfo(array $orderedMaps, array $issuesPerMap, array &$tableContent, array &$tableHeader)
    {
        //count issue status per map
        $countsPerElement = [];
        foreach ($orderedMaps as $index => $element) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerMap[$index] as $issue) {
                if ($issue->getClosedAt()) {
                    ++$countPerMap[2];
                } elseif ($issue->getResolvedAt()) {
                    ++$countPerMap[1];
                } else {
                    ++$countPerMap[0];
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

    /**
     * @param Issue[] $issues
     */
    private function addTableByMap(Report $report, array $issues)
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
        $this->addAggregatedIssuesInfo($orderedMaps, $issuesPerMap, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_map', [], 'report'));
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByCraftsman(Report $report, array $issues)
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
        $this->addAggregatedIssuesInfo($orderedCraftsman, $issuesPerCraftsman, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_craftsman', [], 'report'));
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
