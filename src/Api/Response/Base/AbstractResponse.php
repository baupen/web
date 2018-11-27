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

class AbstractResponse
{
    /**
     * AbstractResponse constructor.
     *
     * @param string $apiStatus
     * @param mixed $data
     * @param null $error
     * @param null $message
     */
    public function __construct(string $apiStatus, $data, $message = null, $error = null)
    {
        $this->status = $apiStatus;
        $this->data = $data;
        $this->error = $error;
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return 1;
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

    /**
     * @var mixed
     */
    private $data;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @var string
     */
    private $message;

    /**
     * @var int|null
     */
    private $error;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int|null
     */
    public function getError()
    {
        return $this->error;
    }
}
