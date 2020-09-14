<?php

namespace App\Form;

use App\Entity\IiifServer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IiifServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('name', TextType::class, [
            'label' => 'Nom du serveur',
            'required' => true,
          ])
          ->add('url', TextType::class, [
            'label' => 'URL du serveur',
            'required' => true,
          ])
          ->add('suffixLarge', TextType::class, [
            'label' => 'suffixe large',
            'required' => false,
          ])
          ->add('suffixThumbnail', TextType::class, [
            'label' => 'suffixe miniature',
            'required' => false,
          ])
          ->add('save', SubmitType::class, array(
              'label' => 'send',
              'attr' => [
                'class' => 'btn btn-primary'
              ]
          ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => IiifServer::class,
        ));
    }
}
