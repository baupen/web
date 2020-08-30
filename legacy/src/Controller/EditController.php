<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/edit")
 */
class EditController extends BaseLoginController
{
    /**
     * @Route("", name="edit")
     *
     * @return Response
     */
    public function indexAction()
    {
        $constructionManager = $this->getUser();

        return $this->render('edit/edit.html.twig', ['constructionSites' => $constructionManager->getConstructionSites()]);
    }
}
