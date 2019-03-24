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
use App\Service\Report\ReportElements;
use Symfony\Component\HttpFoundation\ParameterBag;

trait QueryParseTrait
{
    /**
     * @param Filter $filter
     * @param ConstructionSite $constructionSite
     * @param array $query
     *
     * @throws \Exception
     * @throws \Exception
     */
    private function setFilterProperties(Filter $filter, ConstructionSite $constructionSite, $query)
    {
        $parameterBag = new ParameterBag($query);
        if ($parameterBag->getBoolean('onlyMarked')) {
            $filter->setIsMarked(true);
        }
        if ($parameterBag->getBoolean('onlyOverLimit')) {
            $filter->setResponseLimitEnd(new \DateTime());
        }
        $numberText = $parameterBag->get('numberText');
        if (mb_strlen($numberText) > 0) {
            $filter->setNumberText($numberText);
        }

        //parse input to array
        //empty arrays are not considered
        $flattenIdArray = function ($input) {
        };

        $craftsmanParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($craftsmanParameters->getBoolean('enabled')) {
            $allowedCraftsmen = $craftsmanParameters->get('craftsmen', []);
            if (\is_array($allowedCraftsmen) && \count($allowedCraftsmen) > 0) {
                $craftsmanIds = [];
                foreach ($allowedCraftsmen as $item) {
                    $craftsmanIds[] = $item['id'];
                }

                $filter->setCraftsmen($craftsmanIds);
            }
        }

        $mapParameters = new ParameterBag($parameterBag->get('map', []));
        if ($mapParameters->getBoolean('enabled')) {
            $allowedMaps = $craftsmanParameters->get('maps', []);
            if (\is_array($allowedMaps) && \count($allowedMaps) > 0) {
                $filter->setMaps($allowedMaps);
            }
        }

        $tradeParameters = new ParameterBag($parameterBag->get('trade', []));
        if ($tradeParameters->getBoolean('enabled')) {
            $allowedTrades = $tradeParameters->get('trades', []);
            if (\is_array($allowedTrades) && \count($allowedTrades) > 0) {
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
            list($isEnabled, $start, $end) = self::parseTimeFilter($timeParameters, 'registered');
            $filter->setRegistrationStatus($isEnabled);
            $filter->setRegistrationStatus($start);
            $filter->setRegistrationStatus($end);

            list($isEnabled, $start, $end) = self::parseTimeFilter($timeParameters, 'responded');
            $filter->setRespondedStatus($isEnabled);
            $filter->setRespondedStart($start);
            $filter->setRespondedEnd($end);

            list($isEnabled, $start, $end) = self::parseTimeFilter($timeParameters, 'reviewed');
            $filter->setReviewedStatus($isEnabled);
            $filter->setReviewedStart($start);
            $filter->setReviewedEnd($end);
        }
    }

    /**
     * @param ParameterBag $timeParameters
     * @param string $timeFilterKey
     *
     * @return array
     */
    private static function parseTimeFilter(ParameterBag $timeParameters, string $timeFilterKey)
    {
        $filterParameters = new ParameterBag($timeParameters->get($timeFilterKey, []));

        //parse input to null or datetime
        $toDateTime = function ($input) {
            return $input === null || $input === '' ? null : new \DateTime($input);
        };

        if ($filterParameters->getBoolean('active')) {
            $start = $toDateTime($filterParameters->get('start', null));
            $end = $toDateTime($filterParameters->get('end', null));

            return [$start !== null || $end !== null, $start, $end];
        }

        return [false, null, null];
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
