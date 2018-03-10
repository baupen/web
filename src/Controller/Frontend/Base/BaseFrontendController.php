<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Frontend\Base;

use App\Controller\Base\BaseFormController;
use App\Entity\FrontendUser;

class BaseFrontendController extends BaseFormController
{
    /**
     * @return FrontendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }
}
