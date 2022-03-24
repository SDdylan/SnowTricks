<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, array('label' => "Titre", 'required' => true))
            ->add('description', null, array('label' => "Description", 'required' => true))
            ->add('group', EntityType::class, [
               'class' => Group::class,
               'choice_label' => 'title',
                'label' => 'Groupe'
            ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $trick = $event->getData();
            $form = $event->getForm();

            // checks if the Trick object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Trick"
            if (!$trick || null === $trick->getId()) {
                $form->add('media', CollectionType::class, [
                    'entry_type' => MediaFormType::class,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
