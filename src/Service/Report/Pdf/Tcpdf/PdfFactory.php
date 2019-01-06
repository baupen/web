<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Tcpdf;

use App\Service\Interfaces\PathServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Interfaces\PdfFactoryInterface;
use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;

class PdfFactory implements PdfFactoryInterface
{
    /**
     * @var TcpdfServiceInterface
     */
    private $tcpdfService;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @param TcpdfServiceInterface $tcpdfService
     * @param PathServiceInterface $pathService
     */
    public function __construct(TcpdfServiceInterface $tcpdfService, PathServiceInterface $pathService)
    {
        $this->tcpdfService = $tcpdfService;
        $this->pathService = $pathService;
    }

    /**
     * @param string $header
     * @param string $footer
     * @param string $logoPath
     *
     * @return PdfDocumentInterface
     */
    public function create(string $header, string $footer, string $logoPath)
    {
        $pdf = new Pdf($this->tcpdfService);
        $this->tcpdfService->setPageVariables($pdf, $header, $footer, $logoPath);

        $wrapper = new PdfDocument($pdf);
        $this->tcpdfService->assignWrapper($pdf, $wrapper);

        return $wrapper;
    }
}
