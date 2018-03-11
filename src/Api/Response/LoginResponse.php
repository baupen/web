<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Response\Base\BaseResponse;
use App\Entity\AppUser;
use AppTestBundle\Entity\FunctionalTests\User;

class LoginResponse extends BaseResponse
{
    /**
     * @var AppUser $user
     */
    private $user;

    /**
     * @return AppUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param AppUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}