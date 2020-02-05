<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('medias', CheckboxType::class, [
                'label' => 'export_medias',
                'required' => false
            ])
            ->add('transcriptions', CheckboxType::class, [
                'label' => 'export_transcriptions',
                'required' => false
            ])
            ->add('transcriptions_metadatas', CheckboxType::class, [
                'label' => 'export_transcriptions_metadatas',
                'required' => false,
                'help' => "export_transcriptions_metadatas_help"
            ])
            ->add('transcriptions_apply_xsl', CheckboxType::class, [
                'label' => 'export_transcriptions_apply_xsl',
                'required' => false,
                'help' => "export_transcriptions_apply_xsl_help"
            ])
            ->add('transcriptions_list', CheckboxType::class, [
                'label' => 'export_transcriptions_list',
                'required' => false
            ])
            ->add('users_list', CheckboxType::class, [
                'label' => 'export_users_list',
                'required' => false
            ])
            ->add('project_infos', CheckboxType::class, [
                'label' => 'export_project_infos',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'export_launch',
            ]);
        ;
    }
}
