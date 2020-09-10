<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\IiifServer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IIIFImportType extends AbstractType
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $project = $options["project"];

        $builder
          ->add('zip_iiif', FileType::class, [
              'label' => 'xml_add_iiif_file_placeholder',
              'translation_domain' => 'messages',
              'attr' => [
                'accept' => 'application/zip'
              ],
              'mapped' => false
          ])

          ->add('overwrite', CheckboxType::class, [
              'label'    => 'overwrite_iiif',
              'required' => false,
              'data' => false,
              'mapped' => false,
              'help' => 'overwrite_iiif_help'
          ])

          ->add('iiifServer', EntityType::class, [
              'mapped' => false,
              'class' => IiifServer::class,
              'choices' => $project->getIiifServers(),
              'label' => 'server_iiif',
              'translation_domain' => 'messages',
              'choice_label' => 'name',
              'help' => 'server_iiif'
          ])

          ->add('save', SubmitType::class, [
              'attr' => ['class' => 'btn btn-primary', 'id' => "jdlzqdlzq"],
              'label' => 'project_xml_upload_iiif',
          ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
            'project' => null
        ));
    }
}
