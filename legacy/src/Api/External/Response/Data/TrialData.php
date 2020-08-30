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

class TrialData
{
    /**
     * @var TrialUser
     */
    private $trialUser;

    /**
     * LoginData constructor.
     */
    public function __construct(TrialUser $user)
    {
        $this->trialUser = $user;
    }

    public function getTrialUser(): TrialUser
    {
        return $this->trialUser;
    }
}
