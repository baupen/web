<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Api\Share;

use App\Api\Response\Data\MapsData;
use App\Api\Response\Data\Share\Filter\ReadData;
use App\Api\Transformer\Share\Filter\ConstructionSiteTransformer;
use App\Api\Transformer\Share\Filter\FilterTransformer;
use App\Api\Transformer\Share\Filter\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Controller\External\Traits\FilterAuthenticationTrait;
use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\IssueHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/f/{identifier}")
 */
class FilterController extends ApiController
{
    use FilterAuthenticationTrait;

    const INVALID_IDENTIFIER = 'invalid identifier';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/read", name="external_api_share_filter_read", methods={"GET"})
     *
     * @param $identifier
     * @param ConstructionSiteTransformer $constructionSiteTransformer
     * @param FilterTransformer $filterTransformer
     *
     * @return Response
     */
    public function readAction($identifier, ConstructionSiteTransformer $constructionSiteTransformer, FilterTransformer $filterTransformer)
    {
        /** @var Filter $filter */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $filter)) {
            return $this->fail(self::INVALID_IDENTIFIER);
        }

        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($filter->getConstructionSite());

        $data = new ReadData();
        $data->setConstructionSite($constructionSiteTransformer->toApi($constructionSite));
        $data->setFilter($filterTransformer->toApi($identifier));

        return $this->success($data);
    }

    /**
     * @Route("/maps/list", name="external_api_share_filter_maps_list", methods={"GET"})
     *
     * @param $identifier
     * @param MapTransformer $mapTransformer
     *
     * @return Response
     */
    public function mapsListAction($identifier, MapTransformer $mapTransformer)
    {
        /** @var Filter $filter */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $filter)) {
            return $this->fail(self::INVALID_IDENTIFIER);
        }

        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //convert to api format
        $apiMaps = [];
        foreach ($orderedMaps as $key => $map) {
            $apiMap = $mapTransformer->toApi($map, $identifier, $issuesPerMap[$key]);
            $apiMaps[] = $apiMap;
        }

        //output
        $data = new MapsData();
        $data->setMaps($apiMaps);

        return $this->success($data);
    }
}
