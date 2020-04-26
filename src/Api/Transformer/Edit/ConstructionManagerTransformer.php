<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Edit;

use App\Api\Entity\Edit\ExternalConstructionManager;
use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\ConstructionManager;
use Exception;

class ConstructionManagerTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\ConstructionManagerTransformer
     */
    private $constructionManagerTransformer;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Base\ConstructionManagerTransformer $constructionManagerTransformer)
    {
        $this->constructionManagerTransformer = $constructionManagerTransformer;
    }

    /**
     * @param ConstructionManager $entity
     *
     * @throws Exception
     *
     * @return ExternalConstructionManager
     */
    public function toApi($entity)
    {
        $externalConstructionManager = new ExternalConstructionManager($entity->getId());
        $this->constructionManagerTransformer->writeApiProperties($entity, $externalConstructionManager);

        return $externalConstructionManager;
    }
}
