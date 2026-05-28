<?php

namespace App\Service\Interfaces;

use App\Entity\Filter;

interface FilterServiceInterface
{
    public function createFromQuery(array $filters): Filter;
}
