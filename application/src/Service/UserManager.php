<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
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
    private $repository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, ParameterBagInterface $params, UserRepository $repository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->params = $params;
        $this->repository = $repository;
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
        $this->saveUser($user);
    }

    public function createUserFromForm(User $user, bool $isAdmin = false)
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        // all users have the ROLE_USER
        $roles = ['ROLE_USER'];

        if ($isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);
        $token = base64_encode(random_bytes(10));
        $user->setConfirmationToken($token);
        $this->saveUser($user);
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

        $this->saveUser($user);
    }

    public function userCanRenewPassword(string $data)
    {
        // search by email
        $user = $this->repository->findOneBy(['email' => $data]);
        if (!$user) {
            $user = $this->repository->findOneBy(['username' => $data]);
        }
        if ($user && null === $user->getPasswordRequestedAt()) {
            return $user;
        } elseif ($user && null !== $user->getPasswordRequestedAt()) {
            $now = new \DateTime('now');
            $requestedAt = $user->getPasswordRequestedAt();
            $t1 = \StrToTime($now->format('Y-m-d H:i:s'));
            $t2 = \StrToTime($requestedAt->format('Y-m-d H:i:s'));
            $diff = $t1 - $t2;
            $hours = $diff / (60 * 60);
            return $hours > 2;
        }
        return false;
    }

    public function resetPassword(User $user)
    {
        $user->setConfirmationToken(null);
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setUpdatedAt(new \DateTime('now'));
        $this->saveUser($user);
        return true;
    }

    public function anonymizeUserAccount(User $user, string $type)
    {
        $types = ['full', 'partial'];
        if (!in_array($type, $types)) {
            return false;
        }
        $user->setUsername('anonymous-user-'.$user->getId());
        $user->setEmail('anonymous-email-'.$user->getId().'@anonymous.fr');
        $user->setActive(false);
        $user->setPublicMail(false);
        $user->setAnonymous(true);
        $user->setDescription(null);
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        if (null !== $user->getImage()) {
            $user = $this->deleteUserImage($user);
        }

        if ($type === 'full') {
            $user->setFirstname('anonymized-user');
            $user->setLastname('anonymized-user');
        }

        $this->saveUser($user);
        return true;
    }

    public function deleteUserImage(User $user)
    {
        $basePath = $this->params->get('user_files_directory');
        $fullPath = $basePath.DIRECTORY_SEPARATOR.$user->getImage();
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $user->setImage(null);
        return $user;
    }

    public function saveUser(User $user)
    {
        $user->setUpdatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user)
    {
        $this->em->remove($product);
        $this->em->flush();
    }
}
