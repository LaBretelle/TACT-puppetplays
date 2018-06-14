<?php
namespace App\DataFixtures;

use App\Entity\UserStatus;
use App\Entity\ProjectStatus;
use App\Entity\TranscriptionStatus;
use App\Entity\Platform;
use App\Service\AppEnums;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $om)
    {
        // project user statuses
        $transcriber = new UserStatus();
        $transcriber->setName(AppEnums::USER_STATUS_TRANSCRIBER_NAME);
        $transcriber->setDescription(AppEnums::USER_STATUS_TRANSCRIBER_DESC);
        $om->persist($transcriber);
        $validator = new UserStatus();
        $validator->setName(AppEnums::USER_STATUS_VALIDATOR_NAME);
        $validator->setDescription(AppEnums::USER_STATUS_VALIDATOR_DESC);
        $om->persist($validator);
        $manager = new UserStatus();
        $manager->setName(AppEnums::USER_STATUS_MANAGER_NAME);
        $manager->setDescription(AppEnums::USER_STATUS_MANAGER_DESC);
        $om->persist($manager);

        // project statuses
        $projectStatusNew = new ProjectStatus();
        $projectStatusNew->setName(AppEnums::PROJECT_STATUS_NEW_NAME);
        $projectStatusNew->setDescription(AppEnums::PROJECT_STATUS_NEW_DESC);
        $om->persist($projectStatusNew);
        $projectStatusInProgress = new ProjectStatus();
        $projectStatusInProgress->setName(AppEnums::PROJECT_STATUS_IN_PROGRESS_NAME);
        $projectStatusInProgress->setDescription(AppEnums::PROJECT_STATUS_IN_PROGRESS_DESC);
        $om->persist($projectStatusInProgress);
        $projectStatusFinished = new ProjectStatus();
        $projectStatusFinished->setName(AppEnums::PROJECT_STATUS_FINISHED_NAME);
        $projectStatusFinished->setDescription(AppEnums::PROJECT_STATUS_FINISHED_DESC);
        $om->persist($projectStatusFinished);

        // transcription statuses
        $transcriptionStatusNew = new TranscriptionStatus();
        $transcriptionStatusNew->setName(AppEnums::TRANSCRIPTION_STATUS_NONE);
        $om->persist($transcriptionStatusNew);
        $transcriptionStatusInProgress = new TranscriptionStatus();
        $transcriptionStatusInProgress->setName(AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS);
        $om->persist($transcriptionStatusInProgress);
        $transcriptionStatusInReread = new TranscriptionStatus();
        $transcriptionStatusInReread->setName(AppEnums::TRANSCRIPTION_STATUS_IN_REREAD);
        $om->persist($transcriptionStatusInReread);
        $transcriptionStatusValidated = new TranscriptionStatus();
        $transcriptionStatusValidated->setName(AppEnums::TRANSCRIPTION_STATUS_VALIDATED);
        $om->persist($transcriptionStatusValidated);

        // platform properties
        $platform = new Platform();
        $platform->setName('Please change me');
        $platform->setHomeText('This is the default home text...');
        $om->persist($platform);

        $om->flush();
    }
}
