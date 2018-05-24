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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserTypeFull extends AbstractType
{
    private $authChecker;
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, ['label' => 'firstname', 'translation_domain' => 'messages'])
            ->add('lastname', TextType::class, ['label' => 'lastname', 'translation_domain' => 'messages'])
            ->add('email', EmailType::class, ['label' => 'email', 'translation_domain' => 'messages'])
            ->add('username', TextType::class, ['label' => 'username', 'translation_domain' => 'messages'])
            ->add('description', TextareaType::class, ['label' => 'description', 'translation_domain' => 'messages', 'required' => false, 'attr' => ['class' => 'tinymce-enabled']])
            ->add('publicMail', CheckboxType::class, ['label' => 'public_mail', 'translation_domain' => 'messages', 'required' => false])
            ->add('image', FileType::class, ['label' => 'image', 'translation_domain' => 'messages', 'required' => false, 'data_class' => null])
        ;

        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('active', CheckboxType::class, ['label' => 'user_activate_action', 'translation_domain' => 'messages', 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}
