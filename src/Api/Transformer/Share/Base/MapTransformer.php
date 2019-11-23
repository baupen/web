<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Base;

use App\Api\Entity\Base\PublicMap;
use App\Api\Transformer\Base\PublicMapTransformer;
use App\Entity\Map;
use App\Entity\Traits\IdTrait;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class MapTransformer
{
    /**
     * @var PublicMapTransformer
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
     */
    public function __construct(PublicMapTransformer $mapTransformer, IssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->mapTransformer = $mapTransformer;
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    /**
     * @param Map       $entity
     * @param PublicMap $map
     * @param IdTrait[] $issues
     *
     * @return PublicMap
     */
    public function writeApiProperties($entity, $map, string $identifier, array $issues, callable $generateRoute)
    {
        $this->mapTransformer->writeApiProperties($entity, $map);

        //add images
        if ($entity->getFile() !== null) {
            //generate hash from ids of issues
            $hashContent = implode(',', array_map(function ($issue) {
                /* @var IdTrait $issue */
                return $issue->getId();
            }, $issues));
            $hash = hash('sha256', $hashContent);

            //set image urls
            $arguments = ['map' => $map->getId(), 'file' => $entity->getFile()->getId(), 'identifier' => $identifier, 'hash' => $hash];
            $map->setImageShareView(
                $generateRoute($this->router, $arguments + ['size' => ImageServiceInterface::SIZE_SHARE_VIEW])
            );
            $map->setImageFull(
                $generateRoute($this->router, $arguments + ['size' => ImageServiceInterface::SIZE_FULL])
            );
        }

        return $map;
    }
}
