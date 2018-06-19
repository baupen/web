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
     * @var string
     */
    private $status = ApiStatus::SUCCESSFUL;

    /**
     * @var object
     */
    private $data;

    /**
     * @var string
     */
    private $message;
}
