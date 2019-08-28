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

class ProjectAdvancedType extends AbstractType
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('css', TextareaType::class, [
              'label'    => 'project_css',
              'required' => false,
          ])

          ->add('xslt_export', FileType::class, [
              'mapped' => false,
              'label' => 'xslt_export',
              'translation_domain' => 'messages',
              'required' => false,
              'data_class' => null,
          ])

          ->add('json_schema', FileType::class, [
              'mapped' => false,
              'label' => 'json_schema',
              'translation_domain' => 'messages',
              'required' => false,
              'data_class' => null,
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
