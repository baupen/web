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

use App\Service\Report\Document\LayoutFactoryInterface;
use App\Service\Report\IssueReport\Model\AggregatedIssuesContent;
use App\Service\Report\IssueReport\Model\IntroductionContent;
use App\Service\Report\IssueReport\Model\MapContent;

interface IssueReportServiceInterface
{
    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param IntroductionContent $introductionContent
     */
    public function addIntroduction(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, IntroductionContent $introductionContent);

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param AggregatedIssuesContent $aggregatedIssuesContent
     */
    public function addAggregatedIssueTable(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, AggregatedIssuesContent $aggregatedIssuesContent);

    /**
     * @param LayoutFactoryInterface $layoutFactory
     * @param PrintFactoryInterface $printFactory
     * @param MapContent $mapContent
     */
    public function addMap(LayoutFactoryInterface $layoutFactory, PrintFactoryInterface $printFactory, MapContent $mapContent);
}
