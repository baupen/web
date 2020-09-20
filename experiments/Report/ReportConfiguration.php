<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

use App\Entity\Filter;

class ReportConfiguration
{
    /**
     * @var Filter
     */
    private $filter;

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return bool
     */
    public function showRegistrationStatus()
    {
        return null === $this->filter->getRegistrationStatus() || $this->filter->getRegistrationStatus();
    }

    /**
     * @return bool
     */
    public function showRespondedStatus()
    {
        return null === $this->filter->getRespondedStatus() || $this->filter->getRespondedStatus();
    }

    /**
     * @return bool
     */
    public function showReviewedStatus()
    {
        return null === $this->filter->getReviewedStatus() || $this->filter->getReviewedStatus();
    }
}
