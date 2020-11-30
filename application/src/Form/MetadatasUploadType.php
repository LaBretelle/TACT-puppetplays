<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MetadatasUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('metadatasFile', FileType::class, [
              'label' => 'metadatas_add_file_placeholder',
              'translation_domain' => 'messages',
              'mapped' => false,
          ])

          ->add('save', SubmitType::class, [
              'attr' => ['class' => 'btn btn-primary'],
              'label' => 'metadatas_upload',
          ]);
    }
}
