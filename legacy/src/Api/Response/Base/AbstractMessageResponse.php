<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Base;

class AbstractMessageResponse extends AbstractResponse
{
    /**
     * AbstractResponse constructor.
     *
     * @param int|null $errorCode
     */
    public function __construct(string $apiStatus, string $message, $errorCode)
    {
        parent::__construct($apiStatus, null, $message, $errorCode);
    }
}
