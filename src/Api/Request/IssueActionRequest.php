<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;

use App\Api\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class IssueActionRequest extends AuthenticatedRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $issueID;

    /**
     * @return string
     */
    public function getIssueID(): string
    {
        return $this->issueID;
    }

    /**
     * @param string $issueID
     */
    public function setIssueID(string $issueID): void
    {
        $this->issueID = $issueID;
    }
}
