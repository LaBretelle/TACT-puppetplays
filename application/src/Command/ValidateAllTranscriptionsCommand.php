<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class ValidateAllTranscriptionsCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:validate-all-transcriptions')
        ->setDescription('Validate all transcriptions for an existing project')
        ->setHelp('This command allows you to validate all transcriptions for an existing project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Validate all transcriptions',
            '===========================',
            '',
        ]);

        $projectRepository = $this->em->getRepository(Project::class);
        $userRepository = $this->em->getRepository(User::class);

        $helper = $this->getHelper('question');

        $question = new Question('Project id: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the project id is required');
            }

            return $answer;
        });
        $pid = intval($helper->ask($input, $output, $question));

        $project = $projectRepository->find($pid);
        if (!$project) {
            $output->writeln('ko - the project with id '.$pid.' was not found.');
            exit();
        }

        $question = new Question('User id: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the user id is required');
            }

            return $answer;
        });
        $uid = intval($helper->ask($input, $output, $question));

        $user = $userRepository->find($uid);
        if (!$user) {
            $output->writeln('ko - the user with id '.$uid.' was not found.');
            exit();
        }

        $medias = $project->getMedias();
        foreach ($medias as $media) {
            $transcription = $media->getTranscription();
            $log = new TranscriptionLog();
            $log->setTranscription($transcription);
            // explicitly set timezone since docker container wont have the good one
            $log->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $log->setUser($user);
            $log->setName(AppEnums::TRANSCRIPTION_LOG_VALIDATED);
            $this->em->persist($log);
        }
        $this->em->flush();
        $output->writeln('all transcriptions are now validated \o/');
    }
}
