<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Filters;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

class UnmappedConstructionSiteFilter extends SearchFilter
{
    /**
     * {@inheritdoc}
     */
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
