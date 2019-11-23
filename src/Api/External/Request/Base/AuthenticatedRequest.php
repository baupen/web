<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Request\Base;

use App\Api\Request\Base\AbstractRequest;
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

    public function setAuthenticationToken(string $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }
}
