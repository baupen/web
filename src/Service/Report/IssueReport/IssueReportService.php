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

        $columnedLayout->getTransaction()->commit();
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param BuildingBlocksInterface $buildingBlocks
     * @param AggregatedIssuesContent $aggregatedIssuesContent
     */
    public function addAggregatedIssueTable(LayoutFactoryInterface $layoutFactory, BuildingBlocksInterface $buildingBlocks, AggregatedIssuesContent $aggregatedIssuesContent)
    {
        $layout = $layoutFactory->createFullWidthLayout();
        $buildingBlocks->setLayout($layout);

        $buildingBlocks->printTitle($aggregatedIssuesContent->getTableDescription());
        $layout->getTransaction()->commit();

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

        // prepare content
        $tableHeader = array_merge($aggregatedIssuesContent->getIdentifierHeader(), $aggregatedIssuesContent->getIssuesHeader());
        $rowCount = \count($aggregatedIssuesContent->getIdentifierContent());
        $tableContent = [];
        for ($i = 0; $i < $rowCount; ++$i) {
            $tableContent[] = array_merge($aggregatedIssuesContent->getIdentifierContent()[$i], $aggregatedIssuesContent->getIssuesContent()[$i]);
        }

        // print styled table
        $this->printTable($layoutFactory, $buildingBlocks, $tableColumnConfig, $tableHeader, $tableContent);
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param BuildingBlocksInterface $buildingBlocks
     * @param array $tableColumnConfig
     * @param string[] $tableHeader
     * @param string[][] $tableContent
     */
    private function printTable(LayoutFactoryInterface $layoutFactory, BuildingBlocksInterface $buildingBlocks, array $tableColumnConfig, array $tableHeader, array $tableContent)
    {
        // prepare table layout
        $tableLayout = $layoutFactory->createTableLayout($tableColumnConfig);

        $columnLength = \count($tableHeader);

        $row = $tableLayout->startNewRow();
        $buildingBlocks->setLayout($row);

        for ($i = 0; $i < $columnLength; ++$i) {
            $row->setColumn($i);
            $buildingBlocks->printParagraph($tableHeader[$i]);
        }

        $row->getTransaction();

        // terminate layout
        $tableLayout->getTransaction()->commit();
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param BuildingBlocksInterface $buildingBlocks
     * @param MapContent $mapContent
     */
    public function addMap(LayoutFactoryInterface $layoutFactory, BuildingBlocksInterface $buildingBlocks, MapContent $mapContent)
    {
        $groupLayout = $layoutFactory->createGroupLayout();
        $buildingBlocks->setLayout($groupLayout);

        $buildingBlocks->printTitle($mapContent->getMapName());
        $buildingBlocks->printParagraph($mapContent->getMapContext());

        $mapImage = $mapContent->getMapImage();
        if ($mapImage !== null) {
            $buildingBlocks->printImage($mapImage);
        }
        $groupLayout->getTransaction()->commit();

        // prepare table column config
        $tableColumnConfig = [new ColumnConfiguration(ColumnConfiguration::SIZING_BY_TEXT)];
        $columns = \count($mapContent->getIssuesTableHeader());
        for ($i = 1; $i < $columns; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration(ColumnConfiguration::SIZING_EXPAND);
        }

        $this->printTable($layoutFactory, $buildingBlocks, $tableColumnConfig, $mapContent->getIssuesTableHeader(), $mapContent->getIssuesTableContent());

        if (\count($mapContent->getIssueImages()) > 0) {
            $columnLayout = $layoutFactory->createAutoColumnLayout(4);
            $buildingBlocks->setLayout($columnLayout);

            foreach ($mapContent->getIssueImages() as $image) {
                $buildingBlocks->printIssueImage($image->getImagePath(), $image->getNumber());
            }

            $columnLayout->getTransaction()->commit();
        }
    }
}
