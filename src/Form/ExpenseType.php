<?php

namespace App\Form;

use App\Entity\Expense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('payment', NumberType::class, [
            'invalid_message' => 'Valeur incorrecte.',
            'scale' => 2,
            'html5' => true,
            'attr' => ['step' => "0.01"]
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Expense $expense */
            $expense = $event->getData();
            $form = $event->getForm();
            $form->add('expense', NumberType::class, [
                'label' => $expense->getUser()->getUserEvents()[0]->getPseudo(),
                'invalid_message' => 'Valeur incorrecte.',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => "0.01"]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
    }
}
