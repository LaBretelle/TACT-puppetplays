<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManager
{
    private $passwordEncoder;
    private $em;
    private $params;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, ParameterBagInterface $params)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->params = $params;
    }

    public function createUser(string $name, string $email, string $plainPassword, array $roles)
    {
        $user = new User();
        $user->setUsername($name);
        $user->setEmail($email);
        $password = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $user->setRoles($roles);
        // when created via CLI the account is activated
        $user->setActive(true);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function createUserFromForm(User $user, UploadedFile $file = null)
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        // all users have the ROLE_USER
        $user->setRoles(['ROLE_USER']);

        $token = base64_encode(random_bytes(10));
        $user->setConfirmationToken($token);

        if ($file) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->params->get('user_files_directory'),
                $fileName
            );
            $user->setImage($fileName);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}
