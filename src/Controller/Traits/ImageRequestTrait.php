<?php

namespace App\Controller\Traits;

use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\InputBag;

trait ImageRequestTrait
{
    private function getValidImageSizeFromQuery(InputBag $query): string|int|float|bool|null
    {
        $size = $query->get('size', 'thumbnail');
        if (!in_array($size, ImageServiceInterface::VALID_SIZES)) {
            throw $this->createNotFoundException();
        }

        return $size;
    }
}
