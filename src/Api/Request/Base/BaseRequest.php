<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:38 AM
 */

namespace App\Api\Request\Base;


class BaseRequest
{
    /**
     * @var string
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