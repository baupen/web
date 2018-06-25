<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:38 AM
 */

namespace App\Api\Response\Base;

class AbstractResponse
{
    /**
     * AbstractResponse constructor.
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
