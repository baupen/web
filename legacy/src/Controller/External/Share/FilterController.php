<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Share;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\External\Traits\FilterAuthenticationTrait;
use App\Entity\Filter;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/f/{identifier}")
 */
class FilterController extends BaseDoctrineController
{
    use FilterAuthenticationTrait;

    /**
     * @Route("", name="external_share_filter")
     *
     * @param $identifier
     *
     * @throws Exception
     *
     * @return Response
     */
    public function shareAction($identifier)
    {
        /** @var Filter $filter */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $filter)) {
            throw new NotFoundHttpException();
        }

        if (!$filter->isValid()) {
            return $this->render('share/invalid.html.twig');
        }

        $filter->setLastAccessNow();
        $this->fastSave($filter);

        return $this->render('share/filter.html.twig');
    }
}
