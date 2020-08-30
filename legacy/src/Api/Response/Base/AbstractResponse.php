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
     * @var string
     */
    private $status;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int|null
     */
    private $error;

    /**
     * AbstractResponse constructor.
     *
     * @param mixed       $data
     * @param string|null $message
     * @param int|null    $error
     */
    public function __construct(string $apiStatus, $data, $message = null, $error = null)
    {
        $this->status = $apiStatus;
        $this->data = $data;
        $this->error = $error;
        $this->message = $message;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

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
