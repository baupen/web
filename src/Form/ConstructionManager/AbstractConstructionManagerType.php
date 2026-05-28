<?php

namespace App\Form\ConstructionManager;

use App\Entity\ConstructionManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractConstructionManagerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_user',
            'data_class' => ConstructionManager::class,
        ]);
        parent::configureOptions($resolver);
    }
}
