<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Base;

use App\Entity\Filter;

class FilterTransformer
{
    /**
     * @param Filter $source
     * @param \App\Api\Entity\Base\Filter $target
     */
    public function writeApiProperties($source, $target)
    {
    }
}
