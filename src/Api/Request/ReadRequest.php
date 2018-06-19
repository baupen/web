<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AuthenticatedRequest;

class ReadRequest extends AuthenticatedRequest
{
    /**
     * @var ObjectMeta
     */
    private $user;

    /**
     * @var ObjectMeta[]
     */
    private $craftsmen;

    /**
     * @var ObjectMeta[]
     */
    private $buildings;

    /**
     * @var ObjectMeta[]
     */
    private $maps;

    /**
     * @var ObjectMeta[]
     */
    private $issues;
}