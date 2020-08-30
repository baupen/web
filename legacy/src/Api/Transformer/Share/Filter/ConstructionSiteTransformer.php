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

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\ConstructionSite;

class ConstructionSiteTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\ConstructionSiteTransformer
     */
    private $constructionSiteTransformer;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Base\ConstructionSiteTransformer $constructionSiteTransformer)
    {
        $this->constructionSiteTransformer = $constructionSiteTransformer;
    }

    /**
     * @param ConstructionSite $entity
     *
     * @return \App\Api\Entity\Share\Filter\ConstructionSite
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Share\Filter\ConstructionSite($entity->getId());
        $this->constructionSiteTransformer->writeApiProperties($entity, $issue);

        return $issue;
    }
}
