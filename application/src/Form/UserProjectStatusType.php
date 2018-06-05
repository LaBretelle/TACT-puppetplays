<?php

namespace App\Form;

use App\Entity\UserProjectStatus;
use App\Entity\User;
use App\Entity\UserStatus;
use App\Entity\Project;
use App\Form\UserProjectStatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserProjectStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('user', EntityType::class, [
            'class' => User::class,
            'label' => 'user',
            'translation_domain' => 'messages',
            'choice_label' => 'username'
          ])
          ->add('status', EntityType::class, [
            'class' => UserStatus::class,
            'label' => 'status',
            'translation_domain' => 'messages',
            'choice_label' => 'name',
            'choice_translation_domain' => 'fixtures'
          ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => UserProjectStatus::class,
        ));
    }
}
