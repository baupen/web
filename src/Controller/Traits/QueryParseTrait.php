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
            $filter->setLimitStart(new \DateTime());
        }
        $numberText = $parameterBag->get('numberText');
        if (mb_strlen($numberText) > 0) {
            $filter->setNumberText($numberText);
        }

        //parse input to array
        $toArray = function ($input) {
            return is_array($input) ? $input : [];
        };

        $craftsmanParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($craftsmanParameters->getBoolean('enabled', false)) {
            $filter->setCraftsmen($toArray($craftsmanParameters->get('craftsmen', [])));
        }

        $mapParameters = new ParameterBag($parameterBag->get('map', []));
        if ($mapParameters->getBoolean('enabled', false)) {
            $filter->setMaps($toArray($mapParameters->get('maps', [])));
        }

        $tradeParameters = new ParameterBag($parameterBag->get('trade', []));
        if ($tradeParameters->getBoolean('enabled', false)) {
            $allowedTrades = $toArray($tradeParameters->get('trades', []));

            $craftsmanIds = [];
            foreach ($constructionSite->getCraftsmen() as $craftsman) {
                if (in_array($craftsman->getTrade(), $allowedTrades, true) &&
                    ($filter->getCraftsmen() === null || in_array($craftsman->getId(), $filter->getCraftsmen(), true))) {
                    $craftsmanIds[] = $craftsman->getId();
                }
            }
            $filter->setCraftsmen($craftsmanIds);
        }

        $statusParameters = new ParameterBag($parameterBag->get('status', []));
        if ($statusParameters->getBoolean('enabled', false)) {
            $readParameters = new ParameterBag($statusParameters->get('read', []));
            if ($readParameters->getBoolean('active')) {
                $filter->setReadStatus($readParameters->getBoolean('value'));
            }

            //parse input to null or datetime
            $toDateTime = function ($input) {
                return $input === null || $input === '' ? null : new \DateTime($input);
            };

            $registeredParameters = new ParameterBag($statusParameters->get('registered', []));
            if ($registeredParameters->getBoolean('active')) {
                $filter->setRegistrationStart($toDateTime($registeredParameters->get('start', null)));
                $filter->setRegistrationEnd($toDateTime($registeredParameters->get('end', null)));
            }

            $respondedParameters = new ParameterBag($statusParameters->get('responded', []));
            if ($respondedParameters->getBoolean('active')) {
                $filter->setRespondedStatus($respondedParameters->getBoolean('value'));
                $filter->setRespondedStart($toDateTime($respondedParameters->get('start', null)));
                $filter->setRespondedEnd($toDateTime($respondedParameters->get('end', null)));
            }

            $reviewedParameters = new ParameterBag($statusParameters->get('reviewed', []));
            if ($reviewedParameters->getBoolean('active')) {
                $filter->setreviewedStatus($reviewedParameters->getBoolean('value'));
                $filter->setreviewedStart($toDateTime($reviewedParameters->get('start', null)));
                $filter->setreviewedEnd($toDateTime($reviewedParameters->get('end', null)));
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
            return is_array($input) ? $input : [];
        };
        $tableParameters = new ParameterBag($toArray($parameterBag->get('tables', [])));
        $reportElements->setTableByCraftsman($tableParameters->getBoolean('tableByCraftsman', $reportElements->getTableByCraftsman()));
        $reportElements->setTableByTrade($tableParameters->getBoolean('tableByTrade', $reportElements->getTableByTrade()));
        $reportElements->setTableByMap($tableParameters->getBoolean('tableByMap', $reportElements->getTableByMap()));
    }
}
