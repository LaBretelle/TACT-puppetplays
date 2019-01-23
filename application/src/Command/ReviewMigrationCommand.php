<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\ReviewRequestManager;
use App\Service\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class ReviewMigrationCommand extends Command
{
    private $em;
    private $reviewRequestManager;

    public function __construct(EntityManagerInterface $em, ReviewRequestManager $reviewRequestManager, ProjectManager $projectManager)
    {
        $this->em = $em;
        $this->reviewRequestManager = $reviewRequestManager;
        $this->pm = $projectManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:review-migrate')
        ->setDescription('Migrate to new review system')
        ->setHelp('This command generates review request for ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>',
            'Migrate to new review system',
            '======================================================',
            '</info>',
        ]);


        $helper = $this->getHelper('question');

        $question = new Question('Project id: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the project id is required');
            }

            return $answer;
        });
        $pid = intval($helper->ask($input, $output, $question));

        $project = $this->em->getRepository(Project::class)->find($pid);
        if (!$project) {
            $output->writeln('the project with id '.$pid.' was not found.');
            exit();
        }


        $logRepo = $this->em->getRepository(TranscriptionLog::class);
        $logs = $logRepo->getWaitingForOldValidation($project);

        foreach ($logs as $log) {
            $transcription = $log->getTranscription();
            $user = $log->getUser();
            $media = $transcription->getMedia();

            $this->reviewRequestManager->create($transcription, $user);
        }

        $output->writeln([
          '<info>',
          'Done',
          '======================================================',
          '</info>',
        ]);
    }
}
