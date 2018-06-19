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
     */
    public function __construct(string $apiStatus, string $message)
    {
        parent::__construct($apiStatus);
    }

    /**
     * @var string
     */
    private $message;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
