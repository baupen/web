<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Base;

class Map extends BaseEntity
{
    /**
     * @var bool
     */
    private $name;

    /**
     * @var string
     */
    private $context;

    /**
     * @return bool
     */
    public function isName(): bool
    {
        return $this->name;
    }

    /**
     * @param bool $name
     */
    public function setName(bool $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }
}
