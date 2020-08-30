<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Base\Note;

class NotesData
{
    /**
     * @var Note[]
     */
    private $notes;

    /**
     * @return Note[]
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param Note[] $notes
     */
    public function setNotes(array $notes): void
    {
        $this->notes = $notes;
    }
}
