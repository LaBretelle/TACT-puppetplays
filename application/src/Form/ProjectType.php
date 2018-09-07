<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectStatus;
use App\Entity\TeiSchema;
use App\Entity\User;
use App\Form\UserProjectStatusType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectType extends AbstractType
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

          ->add('name', TextType::class, [
            'label' => 'project_name',
            'translation_domain' => 'messages'
          ])

          ->add('description', TextareaType::class, [
            'label' => 'project_description',
            'translation_domain' => 'messages',
            'attr' => [
              'class' => 'tinymce-enabled'
            ]
          ])
          ->add('public', CheckboxType::class, [
              'label'    => 'is_public',
              'required' => false
          ])
          ->add('nbValidation', IntegerType::class, [
              'label'    => 'nb_validation',
              'required' => true,
              'empty_data' => 2,
          ])
          ->add('css', TextareaType::class, [
              'label'    => 'project_css',
              'required' => false,
          ])
          ->add('image', FileType::class, [
              'label' => 'project_image',
              'translation_domain' => 'messages',
              'required' => false,
              'data_class' => null
          ])

          ->add('manager', EntityType::class, [
              'mapped' => false,
              'class' => User::class,
              'label' => 'project_manager',
              'translation_domain' => 'messages',
              'choice_label' => 'username',
          ])

          ->add('teiSchema', EntityType::class, [
              'mapped' => false,
              'class' => TeiSchema::class,
              'label' => 'tei_schema',
              'translation_domain' => 'messages',
              'choice_label' => 'name',
          ])

          ->add('save', SubmitType::class, array(
              'attr' => array('class' => 'save btn btn-primary pull-right'),
              'label' => 'save',
          ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
        ));
    }
}
