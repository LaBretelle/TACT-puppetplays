<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class ImportBenoiteCommand extends Command
{
    private $em;
    private $transcriptionManager;
    private $fileManager;


    public function __construct(EntityManagerInterface $em, TranscriptionManager $transcriptionManager, FileManager $fileManager)
    {
        $this->em = $em;
        $this->transcriptionManager = $transcriptionManager;
        $this->fileManager = $fileManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:import-benoite')
        ->setDescription('Import transcriptions from csv for an existing project')
        ->setHelp('This command allows you to import transcriptions from csv for an existing project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Create Benoite Groult transcriptions from CSV',
            '=============================================',
            '',
        ]);

        $projectRepository = $this->em->getRepository(Project::class);
        $userRepository = $this->em->getRepository(User::class);
        $mediaRepository = $this->em->getRepository(Media::class);

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

        $question = new ChoiceQuestion(
          'Last question... What status do you want for the newly imported transcription ? (defaults to created)',
          array('created', 'validated'),
          0
        );
        $question->setErrorMessage('Status %s is invalid.');
        $satus = $helper->ask($input, $output, $question);

        $statuses = [
          'created' => AppEnums::TRANSCRIPTION_LOG_CREATED,
          'validated' => AppEnums::TRANSCRIPTION_LOG_VALIDATED
        ];
        $statusName = $statuses[$satus];
        // get project path
        $csvPath = $this->fileManager->getProjectPath($project);
        // open csv
        if (($handle = fopen($csvPath.DIRECTORY_SEPARATOR.'transcriptions.csv', 'r')) !== false) {
            // parse csv
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $xml = $data[0];
                if ($xml && $xml !== '') {
                    $media_name = basename($data[1], '.xml');
                    $output->writeln('media '.$media_name.' \r\n');
                    // foreach line find the corresponding media
                    $media = $mediaRepository->findOneBy(['name' => $media_name]);
                    if ($media) {
                        // create a transcription for this media
                        $transcription = new Transcription();
                        $transcription->setContent($xml);
                        // create a transcription log for each transcription
                        $log = new TranscriptionLog();
                        $log->setTranscription($transcription);
                        $log->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                        $log->setUser($user);
                        $log->setName($statusName);
                        $transcription->addTranscriptionLog($log);
                        $media->setTranscription($transcription);
                        $this->em->persist($media);
                    } else {
                        $output->writeln('no media for name '.$media_name.' \r\n');
                    }
                }
            }
            fclose($handle);
            $this->em->flush();
            $output->writeln('transcriptions were imported \o/');
        }
    }
}
