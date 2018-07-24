<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Note;

use App\Api\Entity\Note\UpdateNote;
use App\Api\Request\ConstructionSiteRequest;

class UpdateNoteRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateNote
     */
    private $note;

    /**
     * @return UpdateNote
     */
    public function getNote(): UpdateNote
    {
        return $this->note;
    }

    /**
     * @param UpdateNote $note
     */
    public function setNote(UpdateNote $note): void
    {
        $this->note = $note;
    }
}
