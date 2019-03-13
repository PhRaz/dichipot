<?php

namespace App\Form;


use App\Entity\UserEvent;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', UserType::class, [
                'label' => false
            ])
        ->add('administrator', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => UserEvent::class]);
    }
}
