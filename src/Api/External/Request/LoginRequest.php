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

use App\Api\External\Request\Base\AbstractRequest;
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
