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

use App\Service\Report\Pdf\Interfaces\DocumentInterface;
use App\Service\Report\Pdf\Interfaces\TcpdfServiceInterface;
use TCPDF;

class CleanPdf extends TCPDF implements DocumentInterface
{
    /**
     * @var TcpdfServiceInterface
     */
    private $tcpdfService;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * CleanPdf constructor.
     *
     * @param TcpdfServiceInterface $tcpdfService
     */
    public function __construct(TcpdfServiceInterface $tcpdfService)
    {
        parent::__construct();

        $this->tcpdfService = $tcpdfService;
        $this->tcpdfService->initialize($this);
    }

    /**
     * @param string $title
     * @param string $author
     * @param string $logoPath
     */
    public function setMeta(string $title, string $author, string $logoPath)
    {
        $this->title = $title;
        $this->author = $author;
        $this->logoPath = $logoPath;

        $this->tcpdfService->setMeta($this, $this->title, $this->author);
    }

    /**
     * logo right & text left.
     */
    public function Header()
    {
        $this->tcpdfService->printHeader($this, $this->title, $this->logoPath);
    }

    /**
     * bottom left author.
     */
    public function Footer()
    {
        $this->tcpdfService->printFooter($this, $this->author);
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
}
