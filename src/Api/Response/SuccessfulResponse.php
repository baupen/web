<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Response\Base\AbstractResponse;
use App\Enum\ApiStatus;

/**
 * for successful requests
 *
 * Class SuccessfulResponse
 * @package App\Api\Response
 */
class SuccessfulResponse extends AbstractResponse
{
    public function __construct($data)
    {
        parent::__construct(ApiStatus::SUCCESSFUL);
        $this->data = $data;
    }

    /**
     * @var object
     */
    private $data;

    /**
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }
}