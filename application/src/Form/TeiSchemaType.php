<?php

namespace App\Form;

use App\Entity\TeiSchema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeiSchemaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'schema_name', 'translation_domain' => 'messages'])
            ->add('json', TextareaType::class, ['label' => 'schema_json', 'translation_domain' => 'messages', 'required' => false])
            ->add('enabled', CheckboxType::class, ['label' => 'schema_enabled', 'translation_domain' => 'messages', 'required' => false, 'data_class' => null])
            ->add('save', SubmitType::class, array(
                'attr' => array('class' => 'save btn btn-primary pull-right'),
                'label' => 'save',
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => TeiSchema::class,
        ));
    }
}
