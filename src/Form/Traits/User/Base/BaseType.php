<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/25/18
 * Time: 8:21 PM
 */

namespace App\Form\Traits\User\Base;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_user',
        ]);
    }
}