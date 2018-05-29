<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\UserProjectStatusType;
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
          ->add('shortDescription', TextareaType::class, [
            'label' => 'project_short_description',
            'translation_domain' => 'messages',
            'attr' => [
              'class' => 'tinymce-enabled'
            ]
          ])
          ->add('status', EntityType::class, [
            'class' => ProjectStatus::class,
            'label' => 'project_status',
            'translation_domain' => 'messages',
            'choice_label' => 'name',
          ])
          ->add('public', CheckboxType::class, array(
              'label'    => 'is_public',
              'required' => false
          ))

          ->add('save', SubmitType::class, array(
              'attr' => array('class' => 'save btn btn-primary pull-right'),
              'label' => 'save',
          ));

        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('userStatuses', CollectionType::class, [
            'label' => 'project_user_status',
            'entry_type' => UserProjectStatusType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'mapped' => true,
            'by_reference' => false,
          ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
        ));
    }
}
