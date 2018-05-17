<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $passwordEncoder;
    private $em;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    public function createUser(string $name, string $email, string $plainPassword, array $roles)
    {
        $user = new User();
        $user->setUsername($name);
        $user->setEmail($email);
        $password = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $user->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function createUserFromForm(User $user)
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        // all users have the ROLE_USER
        $user->setRoles(['ROLE_USER']);
        $this->em->persist($user);
        $this->em->flush();
    }
}
