<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\_Switch;

class PermissionsData
{
    /**
     * @var bool
     */
    private $canEditAssignment;

    public function getCanEditAssignment(): bool
    {
        return $this->canEditAssignment;
    }

    public function setCanEditAssignment(bool $canEditAssignment): void
    {
        $this->canEditAssignment = $canEditAssignment;
    }
}
