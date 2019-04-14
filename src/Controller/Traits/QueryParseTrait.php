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
            $anyStatusValue = 0;

            if ($statusParameters->getBoolean('registered')) {
                $anyStatusValue = $anyStatusValue | Filter::STATUS_REGISTERED;
            }

            if ($statusParameters->getBoolean('read')) {
                $anyStatusValue = $anyStatusValue | Filter::STATUS_READ;
            }

            if ($statusParameters->getBoolean('responded')) {
                $anyStatusValue = $anyStatusValue | Filter::STATUS_RESPONDED;
            }

            if ($statusParameters->getBoolean('reviewed')) {
                $anyStatusValue = $anyStatusValue | Filter::STATUS_REVIEWED;
            }

            if ($anyStatusValue > 0) {
                $filter->setAnyStatus($anyStatusValue);
            }
        }

        //check filtering of status
        $timeParameters = new ParameterBag($parameterBag->get('time', []));
        if ($timeParameters->getBoolean('enabled', false)) {
            list($isEnabled1, $start1, $end1) = self::parseTimeFilter($timeParameters, 'registered');
            list($isEnabled2, $start2, $end2) = self::parseTimeFilter($timeParameters, 'responded');
            list($isEnabled3, $start3, $end3) = self::parseTimeFilter($timeParameters, 'reviewed');

            if ($isEnabled1 || $isEnabled2 || $isEnabled3) {
                $filter->setRegistrationStatus($isEnabled1);
                $filter->setRegistrationStatus($start1);
                $filter->setRegistrationStatus($end1);

                $filter->setRespondedStatus($isEnabled2);
                $filter->setRespondedStart($start2);
                $filter->setRespondedEnd($end2);

                $filter->setReviewedStatus($isEnabled3);
                $filter->setReviewedStart($start3);
                $filter->setReviewedEnd($end3);
            }
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
