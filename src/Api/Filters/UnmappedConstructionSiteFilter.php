<?php

namespace App\Api\Filters;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

class UnmappedConstructionSiteFilter extends SearchFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'constructionSite' => [
                'property' => 'constructionSite',
                'type' => 'string',
                'required' => true,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
        ];
    }
}
