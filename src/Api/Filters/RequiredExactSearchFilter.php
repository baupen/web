<?php

namespace App\Api\Filters;

class RequiredExactSearchFilter extends ExactSearchFilter
{
    public function getDescription(string $resourceClass): array
    {
        $description = parent::getDescription($resourceClass);

        foreach ($description as $entry) {
            $entry['required'] = true;
        }

        return $description;
    }
}
