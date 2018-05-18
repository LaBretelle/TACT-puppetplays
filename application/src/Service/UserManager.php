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

    public function createUser(string $lastname, string $firstname, string $username, string $email, string $plainPassword, array $roles)
    {
        $user = new User();
        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setUsername($username);
        $user->setEmail($email);
        $password = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $user->setRoles($roles);
        // when created via CLI the account is activated
        $user->setActive(true);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function createUserFromForm(User $user)
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        // all users have the ROLE_USER
        $user->setRoles(['ROLE_USER']);

        $token = base64_encode(random_bytes(10));
        $user->setConfirmationToken($token);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Apply changes on all user fields except for password
     * @param  User   $user
     * @param  UploadedFile $file
     */
    public function updateUser(User $user, UploadedFile $file = null, string $previous_image = null)
    {
        if ($file) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $filePath = $this->params->get('user_files_directory');
            // moves the file to the directory where brochures are stored
            $file->move(
                $filePath,
                $fileName
            );
            $user->setImage($fileName);

            if ($previous_image && file_exists($filePath.DIRECTORY_SEPARATOR.$previous_image)) {
                unlink($filePath.DIRECTORY_SEPARATOR.$previous_image);
            }
        } elseif ($previous_image) {
            // explicitly re set the value, but no need to upload image... it's already there
            $user->setImage($previous_image);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}
