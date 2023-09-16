<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Filters;

class RequiredExactSearchFilter extends ExactSearchFilter
{
    public function getDescription(string $resourceClass): array
    {
        $description = parent::getDescription($resourceClass);

        foreach ($description as $key => $entry) {
            $entry['required'] = true;
        }

        return $description;
    }
}
