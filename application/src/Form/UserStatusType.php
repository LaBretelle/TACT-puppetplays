<?php

namespace App\Form;

use App\Entity\UserProjectStatus;
use App\Entity\UserStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('status', EntityType::class, [
            'class' => UserStatus::class,
            'label' => 'status',
            'translation_domain' => 'messages',
            'choice_label' => 'name',
            'choice_translation_domain' => 'fixtures'
          ])
          ->add('save', SubmitType::class, array(
              'attr' => array('class' => 'save pull-right'),
              'label' => 'save',
          ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => UserProjectStatus::class,
        ));
    }
}
