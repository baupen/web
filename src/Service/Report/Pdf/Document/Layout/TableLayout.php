<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document\Layout;

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class TableLayout implements TableLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var Table
     */
    private $tableConfiguration;

    /**
     * @var TableColumn[]
     */
    private $tableColumnConfiguration;

    /**
     * @param PdfDocumentInterface $pdfDocument
     * @param Table $tableConfiguration
     * @param array $tableColumnConfiguration
     */
    public function __construct(PdfDocumentInterface $pdfDocument, Table $tableConfiguration, array $tableColumnConfiguration)
    {
        $this->pdfDocument = $pdfDocument;
        $this->tableConfiguration = $tableConfiguration;
        $this->tableColumnConfiguration = $tableColumnConfiguration;
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        // TODO: Implement endLayout() method.
    }

    /**
     * @param string[] $header
     */
    public function printHeader(array $header)
    {
        // TODO: Implement printHeader() method.
    }

    /**
     * @param string[] $row
     *
     * @return mixed
     */
    public function printRow(array $row)
    {
        // TODO: Implement printRow() method.
    }
}
