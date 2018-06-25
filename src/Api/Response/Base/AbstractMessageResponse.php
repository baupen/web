<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:38 AM
 */

namespace App\Api\Response\Base;

class AbstractMessageResponse extends AbstractResponse
{
    /**
     * AbstractResponse constructor.
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
