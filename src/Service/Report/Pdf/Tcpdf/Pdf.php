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

use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;
use TCPDF;

/**
 * Overrides functionality of @TCPDF which can't be changed otherwise, and relays them to the @TcpdfService.
 *
 * Class Pdf
 */
class Pdf extends TCPDF
{
    /**
     * @var TcpdfServiceInterface
     */
    private $tcpdfService;

    /**
     * @var string
     */
    private $identifier;

    /**
     * CleanPdf constructor.
     *
     * @param TcpdfServiceInterface $tcpdfService
     */
    public function __construct(TcpdfServiceInterface $tcpdfService)
    {
        parent::__construct();
        $this->tcpdfService = $tcpdfService;
        $this->identifier = uniqid();
    }

    /**
     * logo right & text left.
     */
    public function Header()
    {
        $this->tcpdfService->printHeader($this);
    }

    /**
     * bottom left author.
     */
    public function Footer()
    {
        $this->tcpdfService->printFooter($this, (int)$this->getAliasNumPage(), (int)$this->getAliasNbPages());
    }

    /**
     * adds a new page if needed.
     *
     * @param int $h
     * @param string $y
     * @param bool $addpage
     *
     * @return bool|void
     */
    public function checkPageBreak($h = 0, $y = '', $addpage = true)
    {
        parent::checkPageBreak($h, $y, $addpage);
    }

    /**
     * the height to reach after which a new page will begin automatically.
     *
     * @return float
     */
    public function getMaxContentHeight()
    {
        return $this->PageBreakTrigger;
    }

    /**
     * the height to reach after which a new page will begin automatically.
     *
     * @return float
     */
    public function getContentStart()
    {
        return $this->tMargin;
    }

    /**
     * @param $msg
     *
     * @throws \Exception
     */
    public function Error($msg)
    {
        throw new \Exception($msg);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
