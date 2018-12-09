<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Model\Point;

class PointTransformer extends BatchTransformer
{
    /**
     * @param Point $model
     *
     * @return \App\Api\External\Entity\Point
     */
    public function toApi($model)
    {
        $point = new \App\Api\External\Entity\Point();
        $point->setX($model->x);
        $point->setY($model->y);

        return $point;
    }
}
