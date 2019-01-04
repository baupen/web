<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services\Sync\Model;

class ChildModel
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ChildModel|null
     */
    private $parent;

    /**
     * ChildModel constructor.
     *
     * @param int $id
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ChildModel|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param ChildModel|null $parent
     */
    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }
}
