<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Traits;

use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageRequestTrait
{
    private function getValidImageSizeFromQuery(InputBag $query)
    {
        $size = $query->get('size', 'thumbnail');
        if (!in_array($size, ImageServiceInterface::VALID_SIZES)) {
            throw new NotFoundHttpException();
        }

        return $size;
    }
}
