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

use App\Service\Report\IssueReport\Interfaces\IssueReportServiceInterface;
use App\Service\Report\IssueReport\Interfaces\PrintFactoryInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\MapContent;
use function count;
use PdfGenerator\Layout\Configuration\ColumnConfiguration;
use PdfGenerator\Layout\TableRowLayoutInterface;
use PdfGenerator\LayoutFactoryInterface;
use PdfGenerator\Transaction\TransactionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class IssueReportService implements IssueReportServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * IssueReportService constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param IntroductionContent $introductionContent
     */
    public function addIntroduction(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, IntroductionContent $introductionContent)
    {
        //three or two column layout
        $columnedLayout = $layoutFactory->createColumnLayout(3);
        $printer = $printFactory->getPrinter($columnedLayout);

        //image
        if ($introductionContent->getConstructionSiteImage() !== null) {
            $printer->printImage($introductionContent->getConstructionSiteImage());
        }

        $columnedLayout->setColumn(1);

        $printer->printTitle($introductionContent->getConstructionSiteName());
        $printer->printParagraph(implode(', ', $introductionContent->getConstructionSiteAddressLines()));

        $reportElementsTitle = $this->translator->trans('introduction.elements', [], 'report');
        $printer->printTitle($reportElementsTitle);
        $printer->printParagraph(implode(', ', $introductionContent->getReportElements()));

        $columnedLayout->setColumn(2);

        $filterTitle = $this->translator->trans('entity.name', [], 'entity_filter');
        $printer->printTitle($filterTitle);
        $printer->printKeyValueParagraph($introductionContent->getFilterEntries());

        $columnedLayout->getTransaction()->commit();
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param AggregatedIssuesContent $aggregatedIssuesContent
     */
    public function addAggregatedIssueTable(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, AggregatedIssuesContent $aggregatedIssuesContent)
    {
        $layout = $layoutFactory->createFullWidthLayout();
        $printer = $printFactory->getPrinter($layout);

        $printer->printTitle($aggregatedIssuesContent->getTableDescription());
        $layout->getTransaction()->commit();

        // prepare table column config
        $tableColumnConfig = [];
        $normalTableHeadersCount = count($aggregatedIssuesContent->getIdentifierHeader());
        for ($i = 0; $i < $normalTableHeadersCount; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration();
        }

        $statusTableHeaders = $aggregatedIssuesContent->getIssuesHeader();
        $statusTableHeadersCount = count($statusTableHeaders);
        for ($i = 0; $i < $statusTableHeadersCount; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration(ColumnConfiguration::SIZING_BY_TEXT, $statusTableHeaders[$i]);
        }

        // prepare content
        $tableHeader = array_merge($aggregatedIssuesContent->getIdentifierHeader(), $aggregatedIssuesContent->getIssuesHeader());
        $rowCount = count($aggregatedIssuesContent->getIdentifierContent());
        $tableContent = [];
        for ($i = 0; $i < $rowCount; ++$i) {
            $tableContent[] = array_merge($aggregatedIssuesContent->getIdentifierContent()[$i], $aggregatedIssuesContent->getIssuesContent()[$i]);
        }

        // print styled table
        $this->printTable($layoutFactory, $printFactory, $tableColumnConfig, $tableHeader, $tableContent);
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param ColumnConfiguration[] $tableColumnConfig
     * @param string[] $tableHeader
     * @param string[][] $tableContent
     */
    private function printTable(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, array $tableColumnConfig, array $tableHeader, array $tableContent)
    {
        // prepare table layout
        $tableLayout = $layoutFactory->createTableLayout($tableColumnConfig);

        $this->printTableRow($tableLayout->startNewRow(), $printFactory, $tableHeader);

        foreach ($tableContent as $row) {
            $this->printTableRow($tableLayout->startNewRow(), $printFactory, $row);
        }

        $counter = 0;
        $tableLayout->setOnRowCommit(function (TransactionInterface $printTransaction) use (&$counter, $printFactory) {
            if ($counter % 2 === 1) {
                $drawer = $printFactory->getDrawer($printTransaction);
                $drawer->drawTableAlternatingBackground();
            }

            ++$counter;
        });

        // terminate layout
        $tableLayout->getTransaction()->commit();
    }

    /**
     * @param TableRowLayoutInterface $row
     * @param PrintFactoryInterface $printFactory
     * @param string[] $rowContent
     */
    private function printTableRow(TableRowLayoutInterface $row, PrintFactoryInterface $printFactory, array $rowContent)
    {
        $printer = $printFactory->getPrinter($row);

        $columnLength = count($rowContent);
        for ($i = 0; $i < $columnLength; ++$i) {
            $row->setColumn($i);
            $printer->printParagraph($rowContent[$i]);
        }
    }

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param MapContent $mapContent
     */
    public function addMap(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, MapContent $mapContent)
    {
        $groupLayout = $layoutFactory->createGroupLayout();
        $printer = $printFactory->getPrinter($groupLayout);

        $printer->printTitle($mapContent->getMapName());
        $printer->printParagraph($mapContent->getMapContext());

        $mapImage = $mapContent->getMapImage();
        if ($mapImage !== null) {
            $printer->printImage($mapImage);
        }
        $groupLayout->getTransaction()->commit();

        // prepare table column config
        $tableColumnConfig = [new ColumnConfiguration(ColumnConfiguration::SIZING_BY_TEXT, '####')];
        $columns = count($mapContent->getIssuesTableHeader());
        for ($i = 1; $i < $columns; ++$i) {
            $tableColumnConfig[] = new ColumnConfiguration(ColumnConfiguration::SIZING_EXPAND);
        }

        $this->printTable($layoutFactory, $printFactory, $tableColumnConfig, $mapContent->getIssuesTableHeader(), $mapContent->getIssuesTableContent());

        if (count($mapContent->getIssueImages()) > 0) {
            $columnLayout = $layoutFactory->createAutoColumnLayout(4);
            $printer = $printFactory->getPrinter($columnLayout);

            foreach ($mapContent->getIssueImages() as $image) {
                $printer->printIssueImage($image->getImagePath(), $image->getNumber());
            }

            $columnLayout->getTransaction()->commit();
        }
    }
}
