<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:37 AM
 */

namespace App\Api\Request;


use App\Api\Request\Base\AbstractRequest;
use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest extends AbstractRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $passwordHash;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }
}