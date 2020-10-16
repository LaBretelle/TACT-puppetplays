<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          // ->add('target', ChoiceType::class, [
          //   'label' => 'message_target',
          //   'translation_domain' => 'messages',
          //   'choices'  => array(
          //     'all' => 'all',
          //     'reviewer' => 'reviewer',
          //     ),
          // ])
          ->add('content', TextareaType::class, [
            'label' => 'message_content',
            'required' => true,
          ])
          ->add('save', SubmitType::class, [
            'label' => 'message_send',
            'attr' => [
              'class' => 'btn btn-primary'
            ]
          ]);
    }
}
