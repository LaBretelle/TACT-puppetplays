<?php

namespace App\Form;

use App\Entity\Metadata;
use App\Entity\MetadataMedia;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetadataMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('metadata', EntityType::class, [
          'class' => Metadata::class,
          'label' => 'metadata_name',
          'disabled' => true,
          'choice_label' => 'name',
          'query_builder' => function (EntityRepository $er) use ($options) {
              return $er->createQueryBuilder('m')
                          ->andWhere('m.project = '.$options["project"]->getId());
          },
        ])
        ->add('value', TextareaType::class, [
          'label' => "metadata_value",
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MetadataMedia::class,
            'project' => null
        ]);
    }
}
