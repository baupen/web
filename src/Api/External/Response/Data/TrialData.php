<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Response\Data;

use App\Api\External\Entity\TrialUser;
use App\Api\External\Entity\User;

class TrialData
{
    /**
     * LoginData constructor.
     *
     * @param TrialUser $user
     */
    public function __construct(TrialUser $user)
    {
        $this->user = $user;
    }

    /**
     * @var TrialUser
     */
    private $user;

    /**
     * @return TrialUser
     */
    public function getUser(): TrialUser
    {
        return $this->user;
    }
}
