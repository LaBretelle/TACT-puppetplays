<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, ['label' => 'firstname', 'translation_domain' => 'messages'])
            ->add('lastname', TextType::class, ['label' => 'lastname', 'translation_domain' => 'messages'])
            ->add('email', EmailType::class, ['label' => 'email', 'translation_domain' => 'messages'])
            ->add('username', TextType::class, ['label' => 'username', 'translation_domain' => 'messages'])
            ->add('description', TextareaType::class, ['label' => 'description', 'translation_domain' => 'messages', 'required' => false])
            ->add('publicMail', CheckboxType::class, ['label' => 'public_mail', 'translation_domain' => 'messages', 'required' => false])
            ->add('image', FileType::class, ['label' => 'image', 'translation_domain' => 'messages', 'required' => false])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'password', 'translation_domain' => 'messages'],
                'second_options' => ['label' => 'repeat_password', 'translation_domain' => 'messages'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}
