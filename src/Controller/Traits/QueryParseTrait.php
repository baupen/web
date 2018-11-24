<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Traits;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Report\ReportElements;
use Symfony\Component\HttpFoundation\ParameterBag;

trait QueryParseTrait
{
    /**
     * @param Filter $filter
     * @param ConstructionSite $constructionSite
     * @param array $query
     */
    private function setFilterProperties(Filter $filter, ConstructionSite $constructionSite, $query)
    {
        $parameterBag = new ParameterBag($query);
        if ($parameterBag->getBoolean('onlyMarked')) {
            $filter->setIsMarked(true);
        }
        if ($parameterBag->getBoolean('onlyOverLimit')) {
            $filter->setLimitEnd(new \DateTime());
        }
        $numberText = $parameterBag->get('numberText');
        if (mb_strlen($numberText) > 0) {
            $filter->setNumberText($numberText);
        }

        //parse input to array
        //empty arrays are not considered
        $toArray = function ($input) {
            return \is_array($input) && \count($input) > 0 ? $input : null;
        };

        $issueParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($issueParameters->getBoolean('enabled')) {
            $filter->setIssues($toArray($issueParameters->get('issues', [])));
        }

        $craftsmanParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($craftsmanParameters->getBoolean('enabled')) {
            $filter->setCraftsmen($toArray($craftsmanParameters->get('craftsmen', [])));
        }

        $mapParameters = new ParameterBag($parameterBag->get('map', []));
        if ($mapParameters->getBoolean('enabled')) {
            $filter->setMaps($toArray($mapParameters->get('maps', [])));
        }

        $tradeParameters = new ParameterBag($parameterBag->get('trade', []));
        if ($tradeParameters->getBoolean('enabled')) {
            $allowedTrades = $toArray($tradeParameters->get('trades', []));
            if (\is_array($allowedTrades)) {
                $craftsmanIds = [];
                foreach ($constructionSite->getCraftsmen() as $craftsman) {
                    if (\in_array($craftsman->getTrade(), $allowedTrades, true) &&
                        ($filter->getCraftsmen() === null || \in_array($craftsman->getId(), $filter->getCraftsmen(), true))) {
                        $craftsmanIds[] = $craftsman->getId();
                    }
                }
                $filter->setCraftsmen($craftsmanIds);
            }
        }

        //check for status filters
        $statusParameters = new ParameterBag($parameterBag->get('status', []));
        if ($statusParameters->getBoolean('enabled')) {
            $registeredStatus = $statusParameters->getBoolean('registered');
            $readStatus = $statusParameters->getBoolean('read');
            $respondedStatus = $statusParameters->getBoolean('responded');
            $reviewedStatus = $statusParameters->getBoolean('reviewed');

            //only active if at least one set
            if ($registeredStatus || $readStatus || $respondedStatus || $reviewedStatus) {
                $filter->setReadStatus(false);
                $filter->setRespondedStatus(false);
                $filter->setReviewedStatus(false);

                $filter->setReadStatus($readStatus);
                $filter->setRespondedStatus($respondedStatus);
                $filter->setReviewedStatus($reviewedStatus);
            }
        }

        //check filtering of status
        $timeParameters = new ParameterBag($parameterBag->get('time', []));
        if ($timeParameters->getBoolean('enabled', false)) {
            $readParameters = new ParameterBag($timeParameters->get('read', []));
            if ($readParameters->getBoolean('active')) {
                $filter->setReadStatus($readParameters->getBoolean('value'));
            }

            //parse input to null or datetime
            $toDateTime = function ($input) {
                return $input === null || $input === '' ? null : new \DateTime($input);
            };

            $registeredParameters = new ParameterBag($timeParameters->get('registered', []));
            if ($registeredParameters->getBoolean('active')) {
                $filter->setRegistrationStatus(true);
                $filter->setRegistrationStart($toDateTime($registeredParameters->get('start', null)));
                $filter->setRegistrationEnd($toDateTime($registeredParameters->get('end', null)));
            }

            $respondedParameters = new ParameterBag($timeParameters->get('responded', []));
            if ($respondedParameters->getBoolean('active')) {
                $filter->setRespondedStatus(true);
                $filter->setRespondedStart($toDateTime($respondedParameters->get('start', null)));
                $filter->setRespondedEnd($toDateTime($respondedParameters->get('end', null)));
            }

            $reviewedParameters = new ParameterBag($timeParameters->get('reviewed', []));
            if ($reviewedParameters->getBoolean('active')) {
                $filter->setReviewedStatus(true);
                $filter->setReviewedStart($toDateTime($reviewedParameters->get('start', null)));
                $filter->setReviewedEnd($toDateTime($reviewedParameters->get('end', null)));
            }
        }
    }

    /**
     * @param ReportElements $reportElements
     * @param array $query
     */
    private function setReportElements(ReportElements $reportElements, $query)
    {
        $parameterBag = new ParameterBag($query);
        $reportElements->setWithImages($parameterBag->getBoolean('withImages', $reportElements->getWithImages()));

        //parse input to array
        $toArray = function ($input) {
            return \is_array($input) ? $input : [];
        };
        $tableParameters = new ParameterBag($toArray($parameterBag->get('tables', [])));
        $reportElements->setTableByCraftsman($tableParameters->getBoolean('tableByCraftsman', $reportElements->getTableByCraftsman()));
        $reportElements->setTableByTrade($tableParameters->getBoolean('tableByTrade', $reportElements->getTableByTrade()));
        $reportElements->setTableByMap($tableParameters->getBoolean('tableByMap', $reportElements->getTableByMap()));
    }
}
