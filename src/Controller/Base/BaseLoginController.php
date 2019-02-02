<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Base;

use App\Entity\Traits\UserTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseLoginController extends BaseFormController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                'event_dispatcher' => EventDispatcherInterface::class,
                'security.token_storage' => TokenStorageInterface::class,
                'translator' => TranslatorInterface::class,
            ];
    }

    /**
     * @param Request $request
     * @param FormInterface $loginForm
     * @param callable $findEntityCallable
     * @param UserTrait $entity
     *
     * @return FormInterface
     */
    protected function handleLoginForm(Request $request, FormInterface $loginForm, callable $findEntityCallable, $entity)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session !== null && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        // last username entered by the user
        $lastUsername = ($session === null) ? '' : $session->get(Security::LAST_USERNAME);

        if ($error !== null) {
            if ($lastUsername !== null) {
                $constructionManager = $findEntityCallable($lastUsername);
                if ($constructionManager !== null) {
                    $this->displayError($this->getTranslator()->trans('login.errors.password_wrong', [], 'login'));
                } else {
                    $this->displayError($this->getTranslator()->trans('login.errors.email_not_found', [], 'login'));
                }
            } else {
                $this->displayError($this->getTranslator()->trans('login.errors.login_failed', [], 'login'));
            }
        }

        $entity->setEmail($lastUsername);

        $loginForm->setData($entity);
        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted()) {
            throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
        }

        return $loginForm;
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     */
    protected function loginUser(Request $request, UserInterface $user)
    {
        //login programmatically
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }
}
