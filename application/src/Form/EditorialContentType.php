<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorialContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', TextType::class, [
            'disabled' => true,
            'label' => "Nom",
          ])
          ->add('value', TextareaType::class, [
            'label' => "Contenu",
            'attr' => ['class'=>'tinymce-enabled'],
          ])
          ->add('submit', SubmitType::class, array(
              'label' => 'save',
          ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\EditorialContent',
        ));
    }
}
