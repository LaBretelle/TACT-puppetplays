<?php

namespace App\Form;

use App\Entity\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class WebsiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'name', 'translation_domain' => 'messages'])
            ->add('logo', FileType::class, ['label' => 'logo', 'translation_domain' => 'messages', 'required' => false, 'data_class' => null])
            ->add('homeText', TextareaType::class, ['label' => 'home_text', 'translation_domain' => 'messages', 'required' => false, 'attr' => ['class' => 'tinymce-enabled']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Website::class,
        ));
    }
}
