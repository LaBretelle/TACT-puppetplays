<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\UserProjectStatusType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', TextType::class, [
            'label' => 'project_name',
            'translation_domain' => 'messages'
          ])
          ->add('shortDescription', TextareaType::class, [
            'label' => 'project_short_description',
            'translation_domain' => 'messages'
          ])

          ->add('userStatuses', CollectionType::class, [
            'label' => 'project_user_status',
            'entry_type' => UserProjectStatusType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'mapped' => true,
            'by_reference' => false,
          ])

          ->add('save', SubmitType::class, array(
              'attr' => array('class' => 'save'),
              'label' => 'save',
          ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
        ));
    }
}
