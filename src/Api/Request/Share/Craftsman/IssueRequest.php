<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Share\Craftsman;

use Symfony\Component\Validator\Constraints as Assert;

class IssueRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $issueId;

    /**
     * @return string
     */
    public function getIssueId(): string
    {
        return $this->issueId;
    }

    /**
     * @param string $issueId
     */
    public function setIssueId(string $issueId): void
    {
        $this->issueId = $issueId;
    }
}
