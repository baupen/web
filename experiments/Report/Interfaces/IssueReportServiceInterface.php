<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Interfaces;

use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\MapContent;
use PdfGenerator\LayoutFactoryInterface;

interface IssueReportServiceInterface
{
    public function addIntroduction(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, IntroductionContent $introductionContent);

    public function addAggregatedIssueTable(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, AggregatedIssuesContent $aggregatedIssuesContent);

    public function addMap(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, MapContent $mapContent);
}
