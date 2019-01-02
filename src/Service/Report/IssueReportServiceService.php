<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\DocumentLayoutInterface;
use App\Service\Report\Interfaces\IssueReportServiceInterface;
use App\Service\Report\Pdf\Document\PdfPrinter;
use Symfony\Contracts\Translation\TranslatorInterface;

class IssueReportServiceService implements IssueReportServiceInterface
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
     * @param DocumentLayoutInterface $document
     * @param string $constructionSiteName
     * @param string|null $constructionSiteImage
     * @param string $constructionSiteAddressLines
     * @param string $reportElements
     * @param array $filterEntries
     */
    public function addIntroduction(DocumentLayoutInterface $document, string $constructionSiteName, ?string $constructionSiteImage, string $constructionSiteAddressLines, string $reportElements, array $filterEntries)
    {
        //three or two column layout
        $columnedLayout = $document->createColumnLayout(3);

        //image
        if ($constructionSiteImage !== null) {
            $columnedLayout->printImage($constructionSiteImage);
        }

        $columnedLayout->goToColumn(1);

        $columnedLayout->printTitle($constructionSiteName);
        $columnedLayout->printParagraph($constructionSiteAddressLines);

        $reportElementsTitle = $this->translator->trans('introduction.elements', [], 'report');
        $columnedLayout->printTitle($reportElementsTitle);
        $columnedLayout->printParagraph($reportElements);

        $columnedLayout->goToColumn(3);

        $filterTitle = $this->translator->trans('entity.name', [], 'entity_filter');
        $columnedLayout->printTitle($filterTitle);
        $columnedLayout->printKeyValueParagraph($filterEntries);
    }

    /**
     * @param DocumentLayoutInterface $document
     * @param string $tableDescription
     * @param string[] $identifierHeader
     * @param string[] $identifierContent
     * @param string[] $issuesHeader
     * @param string[] $issuesContent
     */
    public function addAggregatedIssueTable(DocumentLayoutInterface $document, string $tableDescription, array $identifierHeader, array $identifierContent, array $issuesHeader, array $issuesContent)
    {
        $fullWidth = $document->createFullWidthLayout();
        $fullWidth->printTitle($tableDescription);
        $fullWidth->endLayout();

        // prepare table config
        $tableConfig = new Table();

        // prepare table column config
        $tableColumnConfig = [];
        $normalTableHeaders = \count($identifierHeader);
        for ($i = 0; $i < $normalTableHeaders; ++$i) {
            $tableColumnConfig[] = new TableColumn();
        }
        $statusTableHeaders = \count($issuesHeader);
        for ($i = 0; $i < $statusTableHeaders; ++$i) {
            $tableColumnConfig[] = new TableColumn(TableColumn::SIZING_BY_HEADER);
        }

        // create table layout
        $tableLayout = $document->createTableLayout($tableConfig, $tableColumnConfig);

        // print header
        $tableHeader = array_merge($identifierHeader, $issuesHeader);
        $tableLayout->printHeader($tableHeader);

        // print content
        $rowCount = \count($identifierContent);
        for ($i = 0; $i < $rowCount; ++$i) {
            $row = array_merge($identifierContent[$i], $issuesContent[$i]);
            $tableLayout->printRow($row);
        }

        // flush
        $tableLayout->endLayout();
    }

    /**
     * @param DocumentLayoutInterface $report
     * @param string $mapName
     * @param string $mapContext
     * @param string|null $mapImage
     * @param string[] $issuesTableHeader
     * @param string[][] $issuesTableContent
     * @param string[] $images
     */
    public function addMap(DocumentLayoutInterface $report, string $mapName, string $mapContext, ?string $mapImage, array $issuesTableHeader, array $issuesTableContent, array $images)
    {
        $groupLayout = $report->createGroupLayout();
        $groupLayout->printTitle($mapName);
        $groupLayout->printParagraph($mapContext);

        if ($mapImage !== null) {
            $groupLayout->printImage($mapImage);
        }
        $groupLayout->endLayout();

        // prepare table config
        $tableConfig = new Table();

        // prepare table column config
        $tableColumnConfig = [new TableColumn(TableColumn::SIZING_BY_HEADER)];
        $columns = \count($issuesTableHeader);
        for ($i = 1; $i < $columns; ++$i) {
            $tableColumnConfig[] = new TableColumn(TableColumn::SIZING_EXPAND);
        }

        // print issue table
        $tableLayout = $report->createTableLayout($tableConfig, $tableColumnConfig);
        $tableLayout->printHeader($issuesTableHeader);
        $tableLayout->printRow($issuesTableContent);
        $tableLayout->endLayout();

        if (\count($images) > 0) {
            $columnLayout = $report->createColumnLayout(4);
            $columnLayout->setAutoColumn();

            foreach ($images as $image) {
                $imagePath = $image['imagePath'];
                $number = $image['number'];

                $columnLayout->printCustom(function ($printer, float $defaultWidth) use ($imagePath, $number) {
                    if ($printer instanceof PdfPrinter) {
                        /* @var PdfPrinter $printer */
                        $printer->printIssueImage($imagePath, $number, $defaultWidth);
                    } else {
                        throw new \Exception('unsupported printer');
                    }
                });
            }
        }
    }
}
