<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 17:17
 */

namespace App\Form\Craftsman;

use App\Entity\AppUser;
use App\Entity\Craftsman;
use App\Entity\FrontendUser;
use App\Entity\Traits\PersonTrait;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\Address\AddressType;
use App\Form\Traits\Communication\CommunicationType;
use App\Form\Traits\Person\PersonType;
use App\Form\Traits\Thing\ThingType;
use App\Form\Traits\User\RegisterType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CraftsmanType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("thing", ThingType::class, ["inherit_data" => true, "label" => false]);
        $builder->add("contact", CommunicationType::class, ["inherit_data" => true, "label" => "trait.name"]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'entity_craftsman',
            'data_class' => Craftsman::class
        ]);
    }
}
