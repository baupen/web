<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document;

use App\Helper\ImageHelper;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Design\TypographyService;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class Printer
{
    /**
     * @var PdfDocumentInterface
     */
    private $document;

    /**
     * @var TypographyService
     */
    private $typography;

    /**
     * @var ColorServiceInterface
     */
    private $color;

    /**
     * @param PdfDocumentInterface $pdfDocument
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(PdfDocumentInterface $pdfDocument, TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
    {
        $this->document = $pdfDocument;
        $this->typography = $typographyService;
        $this->color = $colorService;
    }

    /**
     * @param string $paragraph
     * @param float $width
     */
    public function printParagraph(string $paragraph, float $width = null)
    {
        $this->printText($paragraph, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $title
     * @param float $width
     */
    public function printTitle(string $title, float $width = null)
    {
        $this->printBoldText($title, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $header
     * @param float $width
     */
    public function printRegionHeader(string $header, float $width = null)
    {
        $this->printBoldText($header, $this->typography->getTitleFontSize(), $width);
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     * @param float|null $width
     */
    public function printTable(array $header, array $content, float $width = null)
    {
        $this->document->printTableRow($header, $content, $width);
    }

    /**
     * @param string $filePath
     * @param float $width
     */
    public function printImage(string $filePath, float $width = null)
    {
        $cursor = $this->document->getCursor();

        list($width, $height) = ImageHelper::getWidthHeightArguments($filePath, $width);
        $this->document->printImage($filePath, $width, $height);

        // put cursor below the image
        $this->document->setCursor($cursor[0], $cursor[1] + $height);
    }

    /**
     * @param string[] $keyValues
     * @param float|null $width
     */
    public function printKeyValueParagraph(array $keyValues, float $width = null)
    {
        foreach ($keyValues as $key => $value) {
            $this->printBoldText($key, $this->typography->getTextFontSize(), $width);
            $this->printText($value, $this->typography->getTextFontSize(), $width);
        }
    }

    /**
     * @param string $text
     * @param float $fontSize
     * @param float $width
     */
    private function printText(string $text, float $fontSize, float $width)
    {
        $this->document->configurePrint();
        $this->document->printText($text, $fontSize, $width);
    }

    /**
     * @param string $text
     * @param float $fontSize
     * @param float $width
     */
    private function printBoldText(string $text, float $fontSize, float $width)
    {
        $this->document->configurePrint(['bold' => true]);
        $this->document->printText($text, $fontSize, $width);
    }

    /**
     * @param string $imagePath
     * @param int $number
     * @param float $defaultWidth
     */
    public function printIssueImage(string $imagePath, int $number, float $defaultWidth)
    {
        /** @var PdfDocumentInterface $document */
        $cursor = $this->document->getCursor();
        list($width, $height) = ImageHelper::getWidthHeightArguments($imagePath, $defaultWidth);
        $this->document->printImage($imagePath, $width, $height);
        $afterImageCursor = $this->document->getCursor();

        $this->document->setCursor($cursor[0], $cursor[1]);

        $this->document->configurePrint(['background' => $this->color->getImageOverlayColor()]);
        $this->document->printText((string)$number, $this->typography->getTextFontSize());

        $this->document->setCursor(...$afterImageCursor);
    }
}
