<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:10 AM
 */

namespace App\Api\Entity\Base;


use App\Api\Entity\ObjectMeta;

trait BaseEntity
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