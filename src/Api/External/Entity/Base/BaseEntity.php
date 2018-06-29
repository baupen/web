<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity\Base;

use App\Api\External\Entity\ObjectMeta;
use Symfony\Component\Validator\Constraints as Assert;

class BaseEntity
{
    /**
     * @var ObjectMeta
     *
     * @Assert\NotBlank()
     */
    private $meta;

    /**
     * @return ObjectMeta
     */
    public function getMeta(): ObjectMeta
    {
        return $this->meta;
    }

    /**
     * @param ObjectMeta $meta
     */
    public function setMeta(ObjectMeta $meta): void
    {
        $this->meta = $meta;
    }
}
