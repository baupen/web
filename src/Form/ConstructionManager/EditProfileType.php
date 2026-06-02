<?php

namespace App\Form\ConstructionManager;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditProfileType extends AbstractConstructionManagerType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('givenName', TextType::class);
        $builder->add('familyName', TextType::class);
        $builder->add('phone', TextType::class, ['required' => false]);
    }
}
