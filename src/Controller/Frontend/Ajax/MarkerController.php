<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Frontend\Ajax;

use App\Api\Response\Base\BaseResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Controller\Frontend\Base\BaseFrontendController;
use App\Entity\Building;
use App\Entity\Craftsman;
use App\Entity\Marker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/marker")
 * @Security("has_role('ROLE_FRONTEND_USER')")
 *
 * @return Response
 */
class MarkerController extends BaseDoctrineController
{
    /**
     * @Route("/{marker}/edit", name="frontend_ajax_marker_edit")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Marker $marker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Marker $marker)
    {
        $content = $request->get("content");
        if ($content != "") {
            $marker->setContent($content);
            $this->fastSave($marker);

        }

        return $request->isXmlHttpRequest() ? $this->json(new BaseResponse()) : $this->redirect($request->get("back_url"));
    }
}
