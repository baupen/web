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

class ExactSearchFilter extends SearchFilter
{
    /**
     * {@inheritdoc}
     */
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
