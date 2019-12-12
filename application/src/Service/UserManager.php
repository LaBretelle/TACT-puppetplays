<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;

class UserManager
{
    private $passwordEncoder;
    private $em;
    private $repository;
    private $fileManager;
    private $translator;
    private $security;

    public function __construct(
      UserPasswordEncoderInterface $passwordEncoder,
      EntityManagerInterface $em,
      UserRepository $repository,
      FileManager $fileManager,
      TranslatorInterface $translator,
      Security $security
      ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->repository = $repository;
        $this->fileManager = $fileManager;
        $this->translator = $translator;
        $this->security = $security;
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
            $filePath = $this->fileManager->getUserPath();
            // moves the file to the directory where brochures are stored
            $file->move($filePath, $fileName);
            $user->setImage($fileName);

            if ($previous_image) {
                $this->fileManager->delete($filePath.$previous_image);
            }
        } elseif ($previous_image) {
            // explicitly re set the value, but no need to upload image... it's already there
            $user->setImage($previous_image);
        }

        $this->saveUser($user);

        return;
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
            $requestedAt = $user->getPasswordRequestedAt();
            $diff = $requestedAt->diff(new \DateTime());
            return $diff->h > 2 ? $user : false;
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
        $path = $this->fileManager->getUserPath().$user->getImage();
        $this->fileManager->delete($fullPath);
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
        $this->em->remove($user);
        $this->em->flush();
    }

    public function setRole(User $user, $role)
    {
        $user->setRoles($role);
        $this->saveUser($user);

        return;
    }

    public function exportUserData(User $user)
    {
        $result = [
          'id' => $user->getId(),
          'lastname' => $user->getLastName(),
          'firstname' => $user->getFirstName(),
          'login' => $user->getUserName(),
          'email' => $user->getEmail(),
          'description' => $user->getDescription(),
          'image' => $user->getImage(),
          'roles' => $user->getRoles(),
          'mailIsPublic' => $user->getPublicMail(),
          'accountIsActive' => $user->isActive(),
          'isAnonymous' => $user->isAnonymous(),
          'createdAt' => $user->getCreatedAt(),
          'updatedAt' => $user->getUpdatedAt(),
          'passwordRequestedAt' => $user->getPasswordRequestedAt(),
          'projects' => []
        ];

        $transcriptionLogRepo =  $this->em->getRepository('App:TranscriptionLog');
        foreach ($user->getProjectStatus() as $ups) {
            if ($ups->getEnabled()) {
                $project = $ups->getProject();
                $status = $ups->getStatus();
                $projectToAdd = [
                  'id' => $project->getId(),
                  'name' => $project->getName(),
                  'role' => $this->translator->trans($status->getName(), [], 'fixtures'),
                  'transcriptions' => [],
                  'validations' => []
                ];

                $medias = $project->getMedias();
                foreach ($medias as $media) {
                    $transcription = $media->getTranscription();
                    if ($transcriptionLogRepo->userHasTranscription($transcription, $user)) {
                        $projectToAdd['transcriptions'][] = [
                          'transcriptionId' => $transcription->getId(),
                          'media' => $transcription->getMedia()->getName()
                        ];
                    }

                    if ($transcriptionLogRepo->userHasValidation($transcription, $user)) {
                        $projectToAdd['validations'][] = [
                          'transcriptionId' => $transcription->getId(),
                          'media' => $transcription->getMedia()->getName()
                        ];
                    }
                }

                $result['projects'][] = $projectToAdd;
            }
        }

        return $result;
    }

    public function getCurrentUser()
    {
        return $this->security->getUser();
    }

    public function anonymiseUsers()
    {
        $users = $this->repository->getNonAnonymisedYet();
        foreach ($users as $user) {
            if ($user->getLastAccess()) {
                echo "Anonymisation userId: ".$user->getId()."\n";
                $this->anonymizeUserAccount($user, "full");
            } else {
                echo "Initialisation lastAccess: ".$user->getId()."\n";
                $user->setLastAccess($user->getUpdatedAt());
                $this->em->persist($user);
            }
        }
        $this->em->flush();

        return;
    }
}
