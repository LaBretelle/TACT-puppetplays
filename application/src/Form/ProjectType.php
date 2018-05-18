<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', TextType::class, [
            'label' => 'project_name',
            'translation_domain' => 'messages'
          ])
          ->add('description', TextareaType::class, [
            'label' => 'project_description',
            'translation_domain' => 'messages'
          ])
          ->add('shortDescription', TextareaType::class, [
            'label' => 'project_short_description',
            'translation_domain' => 'messages'
          ])
          ->add('save', SubmitType::class, array(
              'attr' => array('class' => 'save'),
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
