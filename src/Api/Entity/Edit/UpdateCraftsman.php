<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Edit;

class UpdateCraftsman
{
    /**
     * @var string
     */
    private $trade;

    /**
     * @var string
     */
    private $contactName;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $email;

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTrade(): string
    {
        return $this->trade;
    }

    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }
}
