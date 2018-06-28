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
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecoverType extends \App\Form\Traits\User\RecoverType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConstructionManager::class,
        ]);
    }
}
