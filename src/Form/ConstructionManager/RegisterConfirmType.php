<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\ConstructionManager;

use App\Entity\ConstructionManager;
use App\Form\UserTrait\SetPasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterConfirmType extends EditProfileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', EditProfileType::class, ['inherit_data' => true]);
        $builder->add('password', SetPasswordType::class, ['inherit_data' => true]);

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConstructionManager::class,
            'block_name' => 'profile',
        ]);
        parent::configureOptions($resolver);
    }
}
