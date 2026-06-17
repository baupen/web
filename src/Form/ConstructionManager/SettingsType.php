<?php

namespace App\Form\ConstructionManager;

use App\Entity\ConstructionManager;
use App\Form\UserTrait\SetPasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractConstructionManagerType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('receiveWeekly', CheckboxType::class, ['required' => false, 'help' => 'help.receive_weekly']);

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConstructionManager::class,
        ]);
        parent::configureOptions($resolver);
    }
}
