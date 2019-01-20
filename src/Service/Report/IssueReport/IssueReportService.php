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

use App\Service\Report\Document\Interfaces\Configuration\ColumnConfiguration;
use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Document\Interfaces\LayoutFactoryInterface;
use App\Service\Report\IssueReport\Interfaces\BuildingBlocksInterface;
use App\Service\Report\IssueReport\Interfaces\IssueReportServiceInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\MapContent;
use App\Service\Report\Pdf\Layout\TableLayout;
use Symfony\Contracts\Translation\TranslatorInterface;

class IssueReportService implements IssueReportServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param BuildingBlocksInterface $buildingBlocks
     * @param IntroductionContent $introductionContent
     */
    public function addIntroduction(LayoutFactoryInterface $layoutFactory, BuildingBlocksInterface $buildingBlocks, IntroductionContent $introductionContent)
    {
        //three or two column layout
        $columnedLayout = $layoutFactory->createColumnLayout(3);
        $buildingBlocks->setLayout($columnedLayout);

        //image
        if ($introductionContent->getConstructionSiteImage() !== null) {
            $buildingBlocks->printImage($introductionContent->getConstructionSiteImage());
        }

        $columnedLayout->setColumn(1);

        $buildingBlocks->printTitle($introductionContent->getConstructionSiteName());
        $buildingBlocks->printParagraph(implode(', ', $introductionContent->getConstructionSiteAddressLines()));

        $reportElementsTitle = $this->translator->trans('introduction.elements', [], 'report');
        $buildingBlocks->printTitle($reportElementsTitle);
        $buildingBlocks->printParagraph(implode(', ', $introductionContent->getReportElements()));

        $columnedLayout->setColumn(3);

        $filterTitle = $this->translator->trans('entity.name', [], 'entity_filter');
        $buildingBlocks->printTitle($filterTitle);
        $buildingBlocks->printKeyValueParagraph($introductionContent->getFilterEntries());
    }

    /**
     * @param LayoutFactoryInterface $document
     * @param BuildingBlocksInterface $buildingBlocks
     * @param AggregatedIssuesContent $aggregatedIssuesContent
     */
    public function addAggregatedIssueTable(LayoutFactoryInterface $document, BuildingBlocksInterface $buildingBlocks, AggregatedIssuesContent $aggregatedIssuesContent)
    {
        $layout = $document->createFullWidthLayout();
        $buildingBlocks->setLayout($layout);

        $buildingBlocks->printTitle($aggregatedIssuesContent->getTableDescription());
        $layout->getTransaction();

        // prepare table column config
        $tableColumnConfig = [];
        $normalTableHeaders = \count($aggregatedIssuesContent->getIdentifierHeader());
        for ($i = 0; $i < $normalTableHeaders; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration();
        }
        $statusTableHeaders = \count($aggregatedIssuesContent->getIssuesHeader());
        for ($i = 0; $i < $statusTableHeaders; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration(ColumnConfiguration::SIZING_BY_TEXT, $statusTableHeaders[$i]);
        }

        // prepare table layout
        $tableLayout = $document->createTableLayout($tableColumnConfig);

        // prepare content
        $tableHeader = array_merge($aggregatedIssuesContent->getIdentifierHeader(), $aggregatedIssuesContent->getIssuesHeader());
        $rowCount = \count($aggregatedIssuesContent->getIdentifierContent());
        $tableContent = [];
        for ($i = 0; $i < $rowCount; ++$i) {
            $tableContent[] = array_merge($aggregatedIssuesContent->getIdentifierContent()[$i], $aggregatedIssuesContent->getIssuesContent()[$i]);
        }

        // print styled table
        $this->printTable($tableLayout, $buildingBlocks, $tableHeader, $tableContent);

        // terminate layout
        $tableLayout->getTransaction();
    }

    /**
     * @param TableLayoutInterface $tableLayout
     * @param BuildingBlocksInterface $buildingBlocks
     * @param string[] $tableHeader
     * @param string[][] $tableContent
     */
    private function printTable(TableLayoutInterface $tableLayout, BuildingBlocksInterface $buildingBlocks, array $tableHeader, array $tableContent)
    {
        $columnLength = \count($tableHeader);

        $row = $tableLayout->startNewRow();
        $buildingBlocks->setLayout($row);

        for ($i = 0; $i < $columnLength; ++$i) {
            $row->setColumn($i);
            $buildingBlocks->printParagraph($tableHeader[$i]);
        }

        $row->getTransaction();
    }

    /**
     * @param LayoutFactoryInterface $report
     * @param BuildingBlocksInterface $buildingBlocks
     * @param MapContent $mapContent
     */
    public function addMap(LayoutFactoryInterface $report, BuildingBlocksInterface $buildingBlocks, MapContent $mapContent)
    {
        $groupLayout = $report->createGroupLayout();
        $buildingBlocks->setLayout($groupLayout);

        $buildingBlocks->printTitle($mapContent->getMapName());
        $buildingBlocks->printParagraph($mapContent->getMapContext());

        $mapImage = $mapContent->getMapImage();
        if ($mapImage !== null) {
            $buildingBlocks->printImage($mapImage);
        }
        $groupLayout->getTransaction();

        // prepare table column config
        $tableColumnConfig = [new ColumnConfiguration(ColumnConfiguration::SIZING_BY_TEXT)];
        $columns = \count($mapContent->getIssuesTableHeader());
        for ($i = 1; $i < $columns; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration(ColumnConfiguration::SIZING_EXPAND);
        }

        // print issue table
        $tableLayout = $report->createTableLayout($tableColumnConfig);
        $tableLayout->printHeader($mapContent->getIssuesTableHeader());
        // TODO: needs work; row printing not implemented yet
        $tableLayout->printRow($mapContent->getIssuesTableContent());
        $tableLayout->getTransaction();

        if (\count($mapContent->getIssueImages()) > 0) {
            $columnLayout = $report->createColumnLayout(4);
            $columnLayout->setAutoColumn(true);

            foreach ($mapContent->getIssueImages() as $image) {
                $buildingBlocks->printIssueImage($image->getImagePath(), $image->getNumber());
            }
        }
    }
}
