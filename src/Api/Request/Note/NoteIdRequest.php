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

use App\Api\Request\ConstructionSiteRequest;
use Symfony\Component\Validator\Constraints as Assert;

class NoteIdRequest extends ConstructionSiteRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $noteId;

    public function getNoteId(): string
    {
        return $this->noteId;
    }

    public function setNoteId(string $noteId): void
    {
        $this->noteId = $noteId;
    }
}
