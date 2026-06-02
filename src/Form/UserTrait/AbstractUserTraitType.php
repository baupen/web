<?php

namespace App\Form\UserTrait;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractUserTraitType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_user',
        ]);
        parent::configureOptions($resolver);
    }
}
