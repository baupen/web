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

use App\Entity\Note;

class NoteTransformer
{
    /**
     * @param Note $entity
     * @param callable $canEditCallable
     *
     * @return \App\Api\Entity\Base\Note
     */
    public function toApi(Note $entity, callable $canEditCallable)
    {
        $note = new \App\Api\Entity\Base\Note($entity->getId());

        //set props
        $note->setCanEdit($canEditCallable($entity));
        $note->setTimestamp($entity->getLastChangedAt());
        $note->setAuthorName($entity->getCreatedBy()->getName());
        $note->setContent($entity->getContent());

        return $note;
    }

    /**
     * @param \App\Api\Entity\Base\Note $note
     * @param Note $entity
     */
    public function fromApi(\App\Api\Entity\Base\Note $note, Note $entity)
    {
        $entity->setContent($note->getContent());
    }
}
