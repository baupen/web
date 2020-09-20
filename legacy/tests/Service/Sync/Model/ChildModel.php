<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Sync\Model;

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
     * @var ChildModel[]
     */
    private $children = [];

    /**
     * ChildModel constructor.
     *
     * @param self[] $children
     */
    public function __construct(int $id, string $name, array $children = [])
    {
        $this->id = $id;
        $this->name = $name;

        // register parent
        foreach ($children as $child) {
            $child->setParent($this);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

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

    public function setParent(?ChildModel $parent): void
    {
        if (null !== $this->parent) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if (null !== $this->parent) {
            $this->parent->addChild($this);
        }
    }

    /**
     * @return ChildModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    private function removeChild(ChildModel $self)
    {
        unset($this->children[$self->getId()]);
    }

    private function addChild(ChildModel $self)
    {
        $this->children[$self->getId()] = $self;
    }

    /**
     * @return int
     */
    public function countChildren()
    {
        $count = 0;
        foreach ($this->children as $child) {
            $count += $child->countChildren() + 1;
        }

        return $count;
    }
}
