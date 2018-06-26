<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Response\Base;

class AbstractResponse
{
    /**
     * AbstractResponse constructor.
     *
     * @param string $apiStatus
     */
    public function __construct(string $apiStatus)
    {
        $this->status = $apiStatus;
    }

    /**
     * @var string
     */
    private $status;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
