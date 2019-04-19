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

use App\Entity\Filter;
use App\Service\Report\ReportElements;
use DateTime;
use Exception;
use function is_array;
use Symfony\Component\HttpFoundation\ParameterBag;

trait QueryParseTrait
{
    /**
     * @param Filter $filter
     * @param array $query
     *
     * @throws Exception
     */
    private function setFilterProperties(Filter $filter, $query)
    {
        $parameterBag = new ParameterBag($query);
        if ($parameterBag->getBoolean('onlyMarked')) {
            $filter->filterByIsMarked(true);
        }
        if ($parameterBag->getBoolean('onlyOverLimit')) {
            $filter->filterByResponseLimitEnd(new DateTime());
        }

        $craftsmanParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($craftsmanParameters->getBoolean('enabled')) {
            $allowedCraftsmen = $craftsmanParameters->get('craftsmen', []);
            $filter->filterByCraftsmen($allowedCraftsmen);
        }

        $tradeParameters = new ParameterBag($parameterBag->get('trade', []));
        if ($tradeParameters->getBoolean('enabled')) {
            $allowedTrades = $tradeParameters->get('trades', []);
            $filter->filterByTrades($allowedTrades);
        }

        $mapParameters = new ParameterBag($parameterBag->get('map', []));
        if ($mapParameters->getBoolean('enabled')) {
            $allowedMaps = $mapParameters->get('maps', []);
            $filter->filterByMaps($allowedMaps);
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
                $filter->filterByAnyStatus($anyStatusValue);
            }
        }

        //check filtering of status
        $timeParameters = new ParameterBag($parameterBag->get('time', []));
        if ($timeParameters->getBoolean('enabled', false)) {
            list($isEnabled1, $start1, $end1) = self::parseTimeFilter($timeParameters, 'registered');
            if ($isEnabled1) {
                $filter->filterByRegistrationStatus($isEnabled1, $start1, $end1);
            }

            list($isEnabled2, $start2, $end2) = self::parseTimeFilter($timeParameters, 'responded');
            if ($isEnabled2) {
                $filter->filterByRespondedStatus($isEnabled2, $start2, $end2);
            }

            list($isEnabled3, $start3, $end3) = self::parseTimeFilter($timeParameters, 'reviewed');
            if ($isEnabled3) {
                $filter->filterByReviewedStatus($isEnabled3, $start3, $end3);
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
            return $input === null || $input === '' ? null : new DateTime($input);
        };

        if ($filterParameters->getBoolean('enabled')) {
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
            return is_array($input) ? $input : [];
        };
        $tableParameters = new ParameterBag($toArray($parameterBag->get('tables', [])));
        $reportElements->setTableByCraftsman($tableParameters->getBoolean('tableByCraftsman', $reportElements->getTableByCraftsman()));
        $reportElements->setTableByTrade($tableParameters->getBoolean('tableByTrade', $reportElements->getTableByTrade()));
        $reportElements->setTableByMap($tableParameters->getBoolean('tableByMap', $reportElements->getTableByMap()));
    }
}
