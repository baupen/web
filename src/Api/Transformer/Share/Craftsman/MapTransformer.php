<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Craftsman;

use App\Api\Entity\Share\Craftsman\Issue;
use App\Entity\Map;
use App\Entity\Traits\IdTrait;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class MapTransformer
{
    /**
     * @var \App\Api\Transformer\Base\MapTransformer
     */
    private $mapTransformer;

    /**
     * @var IssueTransformer
     */
    private $issueTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\MapTransformer $mapTransformer
     * @param IssueTransformer $issueTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\MapTransformer $mapTransformer, IssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->mapTransformer = $mapTransformer;
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    /**
     * @param Map $entity
     * @param string $identifier
     * @param Issue[] $issues
     *
     * @return \App\Api\Entity\Share\Craftsman\Map
     */
    public function toApi($entity, string $identifier, array $issues)
    {
        $map = new \App\Api\Entity\Share\Craftsman\Map($entity->getId());
        $this->mapTransformer->writeApiProperties($entity, $map);

        //add images
        if ($entity->getFilename() !== null) {
            //generate hash from ids of issues
            $hashContent = $entity->getId() . ',' .
                implode(',', array_map(function ($issue) {
                    /* @var IdTrait $issue */
                    return $issue->getId();
                }, $issues));
            $hash = hash('sha256', $hashContent);

            //set image urls
            $map->setImageShareView(
                $this->router->generate('external_image_craftsman_map',
                    ['map' => $map->getId(), 'identifier' => $identifier, 'hash' => $hash, 'size' => ImageServiceInterface::SIZE_SHARE_VIEW]
                )
            );
            $map->setImageFull(
                $this->router->generate('external_image_craftsman_map',
                    ['map' => $map->getId(), 'identifier' => $identifier, 'hash' => $hash, 'size' => ImageServiceInterface::SIZE_FULL]
                )
            );
        }

        //add issues
        $map->setIssues($this->issueTransformer->toApiMultiple($issues, ['identifier' => $identifier]));

        return $map;
    }
}
