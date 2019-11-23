<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

use App\Api\External\Entity\TrialUser;
use App\Entity\ConstructionManager;

class TrialUserTransformer
{
    /**
     * @return TrialUser
     */
    public function toApi(ConstructionManager $constructionManager)
    {
        $user = new TrialUser();
        $user->setUsername($constructionManager->getEmail());
        $user->setPassword($constructionManager->getPlainPassword());

        return $user;
    }
}
