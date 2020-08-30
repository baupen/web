<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Request;

use App\Api\External\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class IssueActionRequest extends AuthenticatedRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $issueID;

    public function getIssueID(): string
    {
        return $this->issueID;
    }

    public function setIssueID(string $issueID): void
    {
        $this->issueID = $issueID;
    }
}
