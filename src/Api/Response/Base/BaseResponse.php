<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:38 AM
 */

namespace App\Api\Response\Base;


use App\Enum\ApiStatus;

class BaseResponse
{
    /**
     * @var int
     */
    private $apiStatus = ApiStatus::SUCCESSFUL;

    /**
     * @var string
     */
    private $apiErrorMessage;

    /**
     * @return int
     */
    public function getApiStatus()
    {
        return $this->apiStatus;
    }

    /**
     * @param int $apiStatus
     */
    public function setApiStatus($apiStatus)
    {
        $this->apiStatus = $apiStatus;
    }

    /**
     * @return string
     */
    public function getApiErrorMessage()
    {
        return $this->apiErrorMessage;
    }

    /**
     * @param string $apiErrorMessage
     */
    public function setApiErrorMessage($apiErrorMessage)
    {
        $this->apiErrorMessage = $apiErrorMessage;
    }
}