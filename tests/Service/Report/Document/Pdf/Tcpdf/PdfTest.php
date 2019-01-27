<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Report\Document\Pdf\Tcpdf;

use App\Service\Report\Document\Pdf\Configuration\PrintConfiguration;
use App\Service\Report\Document\Pdf\Cursor;
use App\Service\Report\Document\Pdf\PdfDocumentInterface;
use App\Service\Report\Document\Pdf\PdfPageLayoutInterface;
use App\Service\Report\Document\Pdf\Tcpdf\Configuration\TcpdfConfiguration;
use App\Service\Report\Document\Pdf\Tcpdf\Pdf;
use App\Service\Report\Document\Pdf\Tcpdf\PdfDocument;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * PdfTest constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     *
     * @throws \Exception
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $pdfPageLayout = \Mockery::mock(PdfPageLayoutInterface::class, [
            'initializeLayout' => null,
            'printHeader' => null,
            'printFooter' => null,
        ]);

        $this->pdfDocument = new PdfDocument(new Pdf(), $pdfPageLayout);
    }

    public function testGetPdfImplementation_returnsTcpdf()
    {
        $this->assertSame(PdfDocument::PDF_IMPLEMENTATION_TCPDF, $this->pdfDocument->getPdfImplementation());
    }

    public function testGetCursor_returnsFreshCopy()
    {
        $cursor = $this->pdfDocument->getCursor();
        $secondCursor = $this->pdfDocument->getCursor();

        $this->assertTrue($cursor !== $secondCursor);
        $this->cursorMatch($cursor, $secondCursor);
    }

    public function testSetCursor_appliesNewCursor()
    {
        $newCursor = new Cursor(1, 4, 1);
        $this->pdfDocument->setCursor($newCursor);
        $cursor = $this->pdfDocument->getCursor();

        $this->assertTrue($cursor !== $newCursor);
        $this->cursorMatch($cursor, $newCursor);
    }

    public function testGetCursor_noExternalModification()
    {
        $originalCursor = $this->pdfDocument->getCursor();
        $cursor = $this->pdfDocument->getCursor();

        // modify cursor
        $cursor->setX($cursor->getXCoordinate() + 1);
        $cursor->setY($cursor->getYCoordinate() + 1);

        // ensure nothing has changed
        $secondCursor = $this->pdfDocument->getCursor();
        $this->cursorMatch($originalCursor, $secondCursor);
    }

    /**
     * @throws \Exception
     */
    public function testGetConfiguration_returnsFreshCopy()
    {
        $configuration = $this->pdfDocument->getConfiguration();
        $secondConfiguration = $this->pdfDocument->getConfiguration();

        $this->assertTrue($configuration !== $secondConfiguration);
        $this->configurationMatch($configuration, $secondConfiguration);
    }

    /**
     * @throws \Exception
     */
    public function testSetConfiguration_appliesNewConfiguration()
    {
        $newConfiguration = new TcpdfConfiguration();
        $newConfiguration->setConfiguration([PrintConfiguration::FONT_SIZE => 1000]);
        $this->pdfDocument->setConfiguration($newConfiguration);
        $configuration = $this->pdfDocument->getConfiguration();

        $this->assertTrue($configuration !== $newConfiguration);
        $this->configurationMatch($configuration, $newConfiguration);
    }

    /**
     * @throws \Exception
     */
    public function testGetConfiguration_noExternalModification()
    {
        $originalConfig = $this->pdfDocument->getConfiguration();
        $configuration = $this->pdfDocument->getConfiguration();

        // modify configuration
        $configuration->setConfiguration([PrintConfiguration::FONT_SIZE => 1000]);

        // ensure nothing has changed
        $secondConfiguration = $this->pdfDocument->getConfiguration();
        $this->configurationMatch($originalConfig, $secondConfiguration);
    }

    private function cursorMatch(Cursor $expected, Cursor $actual)
    {
        $this->assertSame($expected->getXCoordinate(), $actual->getXCoordinate());
        $this->assertSame($expected->getYCoordinate(), $actual->getYCoordinate());
        $this->assertSame($expected->getPage(), $actual->getPage());
    }

    private function configurationMatch(PrintConfiguration $expected, PrintConfiguration $actual)
    {
        $this->assertArraySubset($expected->getConfiguration(), $actual->getConfiguration());
        $this->assertArraySubset($actual->getConfiguration(), $expected->getConfiguration());
    }
}
