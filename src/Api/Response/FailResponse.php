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
class FailResponse extends AbstractMessageResponse
{
    public function __construct(string $message, int $errorCode)
    {
        parent::__construct(ApiStatus::FAIL, $message, $errorCode);
    }
}
