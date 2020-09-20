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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditProfileType extends AbstractConstructionManagerType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('givenName', TextType::class);
        $builder->add('familyName', TextType::class);
        $builder->add('phone', TextType::class);
    }
}
