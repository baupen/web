<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * allows/disallows application to automatically edit the entity
 */

trait AutomaticEditTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(type="text", options={"default": true})
     */
    private $isAutomaticEditEnabled = true;

    public function getIsAutomaticEditEnabled(): bool
    {
        return $this->isAutomaticEditEnabled;
    }

    public function setIsAutomaticEditEnabled(bool $preventAutomaticEdit): void
    {
        $this->isAutomaticEditEnabled = $preventAutomaticEdit;
    }
}
