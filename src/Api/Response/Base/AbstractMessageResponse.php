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
     * @param string $apiStatus
     * @param string $message
     * @param int $errorCode
     */
    public function __construct(string $apiStatus, string $message, int $errorCode)
    {
        parent::__construct($apiStatus);
        $this->message = $message;
        $this->error = $errorCode;
    }

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $error;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }
}
