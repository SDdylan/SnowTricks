<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, array('label' => false))
            //->add('roles')
            ->add('password', PasswordType::class, array('label' => false))
            ->add('confirm_password', PasswordType::class, array('label' => false))
            ->add('email', null, array('label' => false))
            ->add('lastName', null, array('label' => false))
            ->add('firstName', null, array('label' => false))
            ->add('imageUrl', null, array('label' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
