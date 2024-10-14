<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaptchaType extends AbstractType
{
    public const CAPTCHA_CHALLENGE_SESSION_KEY = '_captcha_challenge';

    public function __construct(private readonly RequestStack $requestStack, private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validate']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $challenge = rand(1, 9);

        $session = $this->requestStack->getSession();
        $session->set(self::CAPTCHA_CHALLENGE_SESSION_KEY, $challenge);

        $view->vars = array_merge($view->vars, ['challenge' => $challenge]);
    }

    public function validate(FormEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $expectedChallenge = $session->get(self::CAPTCHA_CHALLENGE_SESSION_KEY, 0);

        $form = $event->getForm();
        $actualChallenge = $form->getData();

        // see translation files: user always asked to sum up $expectedChallenge + 2
        if (intval($actualChallenge) - 2 !== $expectedChallenge) {
            $error = $this->translator->trans('captcha.invalid', [], 'security');
            $form->addError(new FormError($error));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['mapped' => false]);
    }

    public function getParent(): string
    {
        return NumberType::class;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'captcha';
    }
}
