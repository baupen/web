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
use App\Model\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseFrontendController extends BaseFormController
{
    /**
     * @return FrontendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    abstract protected function getIndexBreadcrumbs();

    /**
     * Renders a view.
     *
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @param Breadcrumb[] $breadcrumbs
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function render(string $view, array $parameters = array(), Response $response = null, array $breadcrumbs = array()) : Response
    {
        $parameters["breadcrumbs"] = array_merge($this->getIndexBreadcrumbs(), $breadcrumbs);
        return parent::render($view, $parameters);
    }
}
