<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Report\Document\Pdf;

use App\Service\Report\Document\Layout\AutoColumnLayoutInterface;
use App\Service\Report\Document\Layout\ColumnLayoutInterface;
use App\Service\Report\Document\Layout\Configuration\ColumnConfiguration;
use App\Service\Report\Document\Layout\FullWidthLayoutInterface;
use App\Service\Report\Document\Layout\GroupLayoutInterface;
use App\Service\Report\Document\Layout\TableLayoutInterface;
use App\Service\Report\Document\Pdf\LayoutFactory;
use App\Service\Report\Document\Pdf\LayoutFactoryConfigurationInterface;
use App\Service\Report\Document\Pdf\PdfDocumentInterface;
use App\Tests\Service\Report\Document\Pdf\Mock\LayoutFactoryConfigurationMock;
use App\Tests\Service\Report\Document\Pdf\Mock\PdfDocumentMock;
use PHPUnit\Framework\TestCase;

class LayoutFactoryTest extends TestCase
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var LayoutFactoryConfigurationInterface
     */
    private $layoutFactoryConfiguration;

    /**
     * LayoutFactoryTest constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->pdfDocument = new PdfDocumentMock();
        $this->layoutFactoryConfiguration = new LayoutFactoryConfigurationMock();
    }

    /**
     * @throws \Exception
     */
    public function testImplementationsReturned()
    {
        $layoutFactory = new LayoutFactory($this->pdfDocument, $this->layoutFactoryConfiguration);

        $this->assertInstanceOf(AutoColumnLayoutInterface::class, $layoutFactory->createAutoColumnLayout(2));
        $this->assertInstanceOf(ColumnLayoutInterface::class, $layoutFactory->createColumnLayout(2));
        $this->assertInstanceOf(FullWidthLayoutInterface::class, $layoutFactory->createFullWidthLayout());
        $this->assertInstanceOf(GroupLayoutInterface::class, $layoutFactory->createGroupLayout());
        $this->assertInstanceOf(TableLayoutInterface::class, $layoutFactory->createTableLayout([new ColumnConfiguration()]));
    }
}
