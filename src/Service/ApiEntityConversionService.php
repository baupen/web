<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:26 PM
 */

namespace App\Service;


use App\Api\Entity\User;
use App\Api\Transformer\UserTransformer;
use App\Entity\ConstructionManager;
use App\Service\Interfaces\ApiEntityConversionServiceInterface;

class ApiEntityConversionService implements ApiEntityConversionServiceInterface
{
    private $userTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
    }

    /**
     * @param ConstructionManager $constructionManager
     * @param string|null $authenticationToken
     *
     * @return User
     */
    public function convertToUser(ConstructionManager $constructionManager, $authenticationToken)
    {
        return $this->userTransformer->transform($constructionManager, $authenticationToken);
    }
}