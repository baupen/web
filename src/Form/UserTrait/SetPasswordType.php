<?php

namespace App\Form\UserTrait;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class SetPasswordType extends AbstractUserTraitType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', PasswordType::class, ['mapped' => false]);
        $builder->add('repeatPlainPassword', PasswordType::class, ['mapped' => false]);
    }
}
