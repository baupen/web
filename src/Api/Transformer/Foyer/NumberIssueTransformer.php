<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Foyer;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Issue;

class NumberIssueTransformer extends BatchTransformer
{
    /**
     * @param Issue $entity
     * @param null $args
     *
     * @return \App\Api\Entity\Foyer\NumberIssue
     */
    public function toApi($entity, $args = null)
    {
        $issue = new \App\Api\Entity\Foyer\NumberIssue($entity->getId());
        $issue->setNumber($entity->getNumber());

        return $issue;
    }
}
