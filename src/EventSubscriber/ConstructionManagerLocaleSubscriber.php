<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\ConstructionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleSubscriber afterwards.
 */
class ConstructionManagerLocaleSubscriber implements EventSubscriberInterface
{
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->setLocaleFromUser($event->getRequest(), $event->getAuthenticationToken()->getUser());
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $this->setLocaleFromUser($event->getRequest(), $event->getTargetUser());
    }

    private function setLocaleFromUser(Request $request, UserInterface $user): void
    {
        if ($request->hasSession() && ($session = $request->getSession()) && $user instanceof ConstructionManager) {
            $session->set('_locale', $user->getLocale());
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}
