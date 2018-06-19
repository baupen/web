<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Response\Base\AbstractMessageResponse;
use App\Enum\ApiStatus;

/**
 * for invalid requests
 *
 * Class FailResponse
 * @package App\Api\Response
 */
class ErrorResponse extends AbstractMessageResponse
{
    public function __construct(string $message)
    {
        parent::__construct(ApiStatus::ERROR, $message);
    }
}