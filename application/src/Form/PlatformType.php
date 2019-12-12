<?php

namespace App\Form;

use App\Entity\Platform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'platform_name', 'translation_domain' => 'messages'])
            ->add('logo', FileType::class, ['label' => 'platform_logo', 'translation_domain' => 'messages', 'required' => false, 'data_class' => null]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Platform::class,
        ));
    }
}
