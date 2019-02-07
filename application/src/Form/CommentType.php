<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentType extends AbstractType
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transcription = $options['transcription'];

        $builder
          ->add('content', TextareaType::class, [
            'label' => false,
            'required' => true,
          ])
          ->add('save', SubmitType::class, array(
              'label' => 'send',
              'attr' => [
                'class' => 'btn btn-primary'
              ]
          ));

        if ($transcription) {
            $route = $this->router->generate('comment_create', ['id' => $transcription]);
            $builder->setAction($route);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Comment::class,
            'transcription' => null
        ));
    }
}
