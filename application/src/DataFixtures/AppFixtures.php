<?php
namespace App\DataFixtures;

use App\Entity\UserStatus;
use App\Entity\ProjectStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Translation\TranslatorInterface;

class AppFixtures extends Fixture
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function load(ObjectManager $om)
    {
        // create project user statuses
        $transcriber = new UserStatus();
        $transcriber->setName($this->translator->trans('user_status_transcriber_name', [], 'fixtures'));
        $transcriber->setDescription($this->translator->trans('user_status_transcriber_desc', [], 'fixtures'));
        $om->persist($transcriber);
        $validator = new UserStatus();
        $validator->setName($this->translator->trans('user_status_validator_name', [], 'fixtures'));
        $validator->setDescription($this->translator->trans('user_status_validator_desc', [], 'fixtures'));
        $om->persist($validator);
        $manager = new UserStatus();
        $manager->setName($this->translator->trans('user_status_manager_name', [], 'fixtures'));
        $manager->setDescription($this->translator->trans('user_status_manager_desc', [], 'fixtures'));
        $om->persist($manager);


        $public = new ProjectStatus();
        $public->setName($this->translator->trans('project_status_public_name', [], 'fixtures'));
        $public->setDescription($this->translator->trans('project_status_public_desc', [], 'fixtures'));
        $om->persist($public);
        $private = new ProjectStatus();
        $private->setName($this->translator->trans('project_status_private_name', [], 'fixtures'));
        $private->setDescription($this->translator->trans('project_status_private_desc', [], 'fixtures'));
        $om->persist($private);

        $om->flush();
    }
}
