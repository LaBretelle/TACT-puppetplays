<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('content', TextareaType::class, [
            'label' => 'message_content',
            'required' => true,
          ])
          ->add('save', SubmitType::class, array(
              'label' => 'message_send',
              'attr' => [
                'class' => 'btn btn-primary'
              ]
          ));
    }
}
