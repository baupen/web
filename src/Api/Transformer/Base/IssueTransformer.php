<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Base;

use App\Entity\Issue;

class IssueTransformer
{
    /**
     * @param Issue $source
     * @param \App\Api\Entity\Base\Issue $target
     */
    public function writeApiProperties($source, $target)
    {
        $target->setMap($source->getMap()->getName());
        $target->setIsMarked($source->getIsMarked());
        $target->setDescription($source->getDescription());
        $target->setWasAddedWithClient($source->getWasAddedWithClient());
        $target->setCraftsmanId($source->getCraftsman()->getId());
        $target->setImageFilePath($source->getImageFilePath());
        $target->setResponseLimit($source->getResponseLimit());
    }

    /**
     * @param \App\Api\Entity\Base\Issue $source
     * @param Issue $target
     */
    public function writeEntityProperties($source, $target)
    {
        $target->setIsMarked($source->getIsMarked());
        $target->setDescription($source->getDescription());
        $target->setWasAddedWithClient($source->getWasAddedWithClient());
        $target->setResponseLimit($source->getResponseLimit());
    }
}
