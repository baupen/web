<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Filter;

use App\Entity\Issue;
use App\Entity\Map;
use Symfony\Component\Routing\RouterInterface;

class MapTransformer
{
    /**
     * @var \App\Api\Transformer\Share\Base\MapTransformer
     */
    private $mapTransformer;

    /**
     * @var IssueTransformer
     */
    private $issueTransformer;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Share\Base\MapTransformer $mapTransformer, IssueTransformer $issueTransformer)
    {
        $this->mapTransformer = $mapTransformer;
        $this->issueTransformer = $issueTransformer;
    }

    /**
     * @param Map     $entity
     * @param Issue[] $issues
     *
     * @return \App\Api\Entity\Share\Filter\Map
     */
    public function toApi($entity, string $identifier, array $issues)
    {
        $map = new \App\Api\Entity\Share\Filter\Map($entity->getId());
        $this->mapTransformer->writeApiProperties($entity, $map, $identifier, $issues, function ($router, $arguments) {
            /* @var RouterInterface $router */
            return $router->generate('external_image_filter_map', $arguments);
        });

        //add issues
        $convertedIssues = [];
        foreach ($issues as $issue) {
            $convertedIssues[] = $this->issueTransformer->toApi($issue, $identifier);
        }
        $map->setIssues($convertedIssues);

        return $map;
    }
}
