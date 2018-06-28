<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectMediaType extends AbstractType
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('images', FileType::class, [
              'multiple' => true,
              //'required' => false,
              'label' => 'project_media_add_file_placeholder',
              'translation_domain' => 'messages',
              'attr' => [
                'accept' => 'image/*',
                'multiple' => 'multiple',
                'id' => 'add_images_input'
              ],
              'mapped' => false
          ])
          ->add('zip', FileType::class, [
              'label' => 'project_media_add_file_placeholder',
              //'required' => false,
              'translation_domain' => 'messages',
              'attr' => [
                'accept' => 'application/zip',
                'id' => 'add_images_input'
              ],
              'mapped' => false
          ])
          ->add('save', SubmitType::class, [
              'attr' => ['class' => 'btn btn-primary'],
              'label' => 'save',
          ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
        ));
    }
}
