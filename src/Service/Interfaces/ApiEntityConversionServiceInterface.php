<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:27 PM
 */

namespace App\Service\Interfaces;


use App\Api\Entity\User;
use App\Entity\ConstructionManager;

interface ApiEntityConversionServiceInterface
{
    /**
     * @param ConstructionManager $constructionManager
     * @param string|null $authenticationToken
     *
     * @return User
     */
    public function convertToUser(ConstructionManager $constructionManager, $authenticationToken);
}