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

use App\Model\Frame;

class FrameTransformer
{
    /**
     * @param Frame $model
     *
     * @return \App\Api\External\Entity\Frame
     */
    public function toApi($model)
    {
        if (null === $model) {
            return null;
        }

        $frame = new \App\Api\External\Entity\Frame();
        $frame->setStartX($model->startX);
        $frame->setStartY($model->startY);
        $frame->setHeight($model->height);
        $frame->setWidth($model->width);

        return $frame;
    }
}
