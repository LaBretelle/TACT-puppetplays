<?php

namespace App\Form;

use App\Entity\Metadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetadataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', TextType::class, [
            'label' => 'metadata_name',
            'required' => true,
          ])
          ->add('defaultValue', TextareaType::class, [
            'label' => 'metadata_default',
            'required' => false,
          ]);

        if ($options["metadata"]) {
            $builder->add('applyTo', ChoiceType::class, [
                'label' => 'metadata_applyto',
                'mapped' => false,
                'required' => true,
                'placeholder' => 'Veuillez sélectionner une option',
                'choices'  => [
                    'choice_metadata_update_same' => 'same',
                    'choice_metadata_update_empty' => 'empty',
                    'choice_metadata_update_all' => 'all',
                ],
            ]);
        }

        $builder->add('save', SubmitType::class, array(
              'label' => 'save',
              'attr' => [
                'class' => 'btn btn-primary'
              ]
          ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Metadata::class,
            'metadata' => null
        ]);
    }
}
