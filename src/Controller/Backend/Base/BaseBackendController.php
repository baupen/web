<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Backend\Base;

use App\Controller\Base\BaseFormController;
use App\Entity\BackendUser;

class BaseBackendController extends BaseFormController
{
    /**
     * @return BackendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }
}
