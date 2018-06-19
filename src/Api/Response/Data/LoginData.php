<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Data\Response;


use App\Api\Entity\User;
use App\Api\Response\Base\AbstractResponse;
use App\Entity\ConstructionManager;

class LoginData extends AbstractResponse
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * @return User
     */
    public function     getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}