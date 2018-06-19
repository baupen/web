<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:38 AM
 */

namespace App\Api\Request\Base;
use Symfony\Component\Validator\Constraints as Assert;


class AuthenticatedRequest extends AbstractRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $authenticationToken;

    /**
     * @return string
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }

    /**
     * @param string $authenticationToken
     */
    public function setAuthenticationToken(string $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }
}