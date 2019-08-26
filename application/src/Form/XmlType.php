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

class XmlType extends AbstractType
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('xmls', FileType::class, [
              'multiple' => true,
              'label' => 'xml_add_file_placeholder',
              'translation_domain' => 'messages',
              'attr' => [
                'multiple' => 'multiple',
                'id' => 'add_xmls_input'
              ],
              'mapped' => false,
          ])
          ->add('zip', FileType::class, [
              'label' => 'xml_add_file_placeholder',
              'translation_domain' => 'messages',
              'attr' => [
                'accept' => 'application/zip',
                'id' => 'add_xmls_input'
              ],
              'mapped' => false
          ])

          ->add('overwrite', CheckboxType::class, [
              'label'    => 'overwrite',
              'required' => false,
              'data' => false,
              'mapped' => false,
              'help' => 'overwrite_help'
          ])

          ->add('create_empty_media', CheckboxType::class, [
              'label'    => 'create_empty_media',
              'required' => false,
              'data' => false,
              'mapped' => false,
              'help' => 'create_empty_media_help'
          ])

          ->add('auto_valid_transcript', CheckboxType::class, [
              'label'    => 'auto_valid_transcript',
              'required' => false,
              'data' => false,
              'mapped' => false,
              'help' => 'auto_valid_transcript_help'
          ])

          ->add('rootTag', TextType::class, [
            'label' => 'rootTag',
            'required' => false,
            'mapped' => false,
            'help' => 'rootTag_help'
          ])

          ->add('save', SubmitType::class, [
              'attr' => ['class' => 'btn btn-primary'],
              'label' => 'project_xml_upload',
          ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
        ));
    }
}
