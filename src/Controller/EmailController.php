<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\Email;
use App\Helper\DoctrineHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/email')]
class EmailController extends BaseController
{
    #[Route(path: '/{identifier}', name: 'email')]
    public function email(string $identifier, ManagerRegistry $registry): Response
    {
        $email = $registry->getRepository(Email::class)->findOneBy(['identifier' => $identifier]);
        if (null === $email) {
            throw new NotFoundHttpException();
        }

        $email->markRead();
        DoctrineHelper::persistAndFlush($registry, $email);

        return $this->render('email/_view_online_base.html.twig', $email->getContext());
    }
}
