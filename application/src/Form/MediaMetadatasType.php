<?php

namespace App\Form;

use App\Entity\Media;
use App\Form\MetadataMediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaMetadatasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('metadatas', CollectionType::class, [
          'entry_type' => MetadataMediaType::class,
          'entry_options' => array('label' => false, 'project' => $options['project']),
          'label' => false
        ])

        ->add('save', SubmitType::class, array(
            'attr' => array('class' => 'save btn btn-primary pull-right'),
            'label' => 'save',
        ))
        ->setAction($options["route"]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'project' => null,
            'route' => null
        ]);
    }
}
