<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\_Switch;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCheckRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $constructionSiteName;

    public function getConstructionSiteName(): string
    {
        return $this->constructionSiteName;
    }

    public function setConstructionSiteName(string $constructionSiteName): void
    {
        $this->constructionSiteName = $constructionSiteName;
    }
}
