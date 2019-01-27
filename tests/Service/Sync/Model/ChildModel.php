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
     * @param int $id
     * @param string $name
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
     * @param self|null $parent
     */
    public function setParent(?self $parent): void
    {
        if ($this->parent !== null) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if ($this->parent !== null) {
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

    /**
     * @param self $self
     */
    private function removeChild(self $self)
    {
        unset($this->children[$self->getId()]);
    }

    /**
     * @param self $self
     */
    private function addChild(self $self)
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
