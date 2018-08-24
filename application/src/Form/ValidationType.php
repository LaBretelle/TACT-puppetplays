<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isValid', ChoiceType::class, [
              'label' => 'transcription_is_valid',
              'translation_domain' => 'messages',
              'choices'  => array(
                'yes' => true,
                'no' => false,
              ),
          ])
          ->add('comment', TextareaType::class, [
            'label' => 'validation_comment',
            'translation_domain' => 'messages',
            'required' => false,
          ])
          ->add('save', SubmitType::class, array(
              'label' => 'submit_validation',
          ))
        ;
    }
}
