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

use App\Controller\Base\BaseFormController;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Form\SupportType;
use App\Service\Interfaces\EmailServiceInterface;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/support")
 *
 * @return Response
 */
class SupportController extends BaseFormController
{
    /**
     * @Route("", name="support")
     *
     * @param Request               $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface   $translator
     * @param LoggerInterface       $logger
     *
     * @return Response
     */
    public function supportAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $createForm = function () {
            return $this->createForm(SupportType::class)
                ->add('support.submit', SubmitType::class, ['translation_domain' => 'support'])
            ;
        };
        $form = $this->handleForm(
            $createForm(),
            $request,
            function ($form) use ($request, $emailService, $translator, $logger, $createForm) {
                /** @var FormInterface $form */
                $name = $form->getData()['name'];
                $email = $form->getData()['email'];
                $message = $form->getData()['message'];

                //create email
                $entity = new Email();
                $entity->setEmailType(EmailType::PLAIN_EMAIL);
                $entity->setReceiver($this->getParameter('SUPPORT_EMAIL'));
                $entity->setSubject($translator->trans('support.email.subject', ['%page%' => $request->getHttpHost()], 'support'));
                $entity->setBody($translator->trans('support.email.body', ['%name%' => $name, '%email%' => $email, '%message%' => $message], 'support'));

                //save & send
                $this->fastSave($entity);
                if ($emailService->sendEmail($entity, ['reply_to' => $email])) {
                    $entity->setSentDateTime(new DateTime());
                    $this->fastSave($entity);

                    $logger->info('sent support email from ' . $email . ' to ' . $entity->getReceiver());
                    $this->displaySuccess($translator->trans('support.success.email_sent', [], 'support'));
                    $form = $createForm();
                } else {
                    $logger->error('could not send support email ' . $entity->getId());
                    $this->displaySuccess($translator->trans('support.fail.email_not_sent', [], 'support'));
                }

                return $form;
            }
        );

        return $this->render('support/support.html.twig', ['form' => $form->createView()]);
    }
}
