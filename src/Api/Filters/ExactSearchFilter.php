<?php

namespace App\Api\Filters;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

class ExactSearchFilter extends SearchFilter
{
    public function getDescription(string $resourceClass): array
    {
        $description = parent::getDescription($resourceClass);

        $filteredDescription = [];
        foreach ($description as $key => $entry) {
            if (str_ends_with($key, '[]')) { // cannot require multiple associations
                continue;
            }

            $filteredDescription[$key] = $entry;
        }

        return $filteredDescription;
    }
}
