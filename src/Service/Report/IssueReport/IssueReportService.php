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

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\LayoutFactoryInterface;
use App\Service\Report\IssueReport\Interfaces\BuildingBlocksInterface;
use App\Service\Report\IssueReport\Interfaces\IssueReportServiceInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\MapContent;
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

        $columnedLayout->goToColumn(1);

        $buildingBlocks->printTitle($introductionContent->getConstructionSiteName());
        $buildingBlocks->printParagraph(implode(', ', $introductionContent->getConstructionSiteAddressLines()));

        $reportElementsTitle = $this->translator->trans('introduction.elements', [], 'report');
        $buildingBlocks->printTitle($reportElementsTitle);
        $buildingBlocks->printParagraph(implode(', ', $introductionContent->getReportElements()));

        $columnedLayout->goToColumn(3);

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
        $layout->endLayout();

        // prepare table config
        $tableConfig = new Table();

        // prepare table column config
        $tableColumnConfig = [];
        $normalTableHeaders = \count($aggregatedIssuesContent->getIdentifierHeader());
        for ($i = 0; $i < $normalTableHeaders; ++$i) {
            $tableColumnConfig[] = new TableColumn();
        }
        $statusTableHeaders = \count($aggregatedIssuesContent->getIssuesHeader());
        for ($i = 0; $i < $statusTableHeaders; ++$i) {
            $tableColumnConfig[] = new TableColumn(TableColumn::SIZING_BY_HEADER);
        }

        // create table layout
        $tableLayout = $document->createTableLayout($tableConfig, $tableColumnConfig);

        // print header
        $tableHeader = array_merge($aggregatedIssuesContent->getIdentifierHeader(), $aggregatedIssuesContent->getIssuesHeader());
        $tableLayout->printHeader($tableHeader);

        // print content
        $rowCount = \count($aggregatedIssuesContent->getIdentifierContent());
        for ($i = 0; $i < $rowCount; ++$i) {
            $row = array_merge($aggregatedIssuesContent->getIdentifierContent()[$i], $aggregatedIssuesContent->getIssuesContent()[$i]);
            $tableLayout->printRow($row);
        }

        // flush
        $tableLayout->endLayout();
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
        $groupLayout->endLayout();

        // prepare table config
        $tableConfig = new Table();

        // prepare table column config
        $tableColumnConfig = [new TableColumn(TableColumn::SIZING_BY_HEADER)];
        $columns = \count($mapContent->getIssuesTableHeader());
        for ($i = 1; $i < $columns; ++$i) {
            $tableColumnConfig[] = new TableColumn(TableColumn::SIZING_EXPAND);
        }

        // print issue table
        $tableLayout = $report->createTableLayout($tableConfig, $tableColumnConfig);
        $tableLayout->printHeader($mapContent->getIssuesTableHeader());
        // TODO: needs work; row printing not implemented yet
        $tableLayout->printRow($mapContent->getIssuesTableContent());
        $tableLayout->endLayout();

        if (\count($mapContent->getIssueImages()) > 0) {
            $columnLayout = $report->createColumnLayout(4);
            $columnLayout->setAutoColumn(true);

            foreach ($mapContent->getIssueImages() as $image) {
                $buildingBlocks->printIssueImage($image->getImagePath(), $image->getNumber());
            }
        }
    }
}
