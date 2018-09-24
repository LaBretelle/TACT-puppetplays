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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class ImportToussaintCommand extends Command
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
        ->setName('app:import-toussaint')
        ->setDescription('Import transcriptions from csv for JP Toussaint')
        ->setHelp('This command allows you to import transcriptions from csv for JP Toussaint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Create transcriptions from JP TOUSSAINT CSV',
            '===========================================',
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

        // get project path
        $csvPath = $this->fileManager->getProjectPath($project);
        // open csv
        if (($handle = fopen($csvPath.DIRECTORY_SEPARATOR.'transcriptions.csv', 'r')) !== false) {
            // parse csv
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $xml = $data[0];
                if ($xml && $xml !== '') {
                    $media_name = basename($data[1], '.xml');
                    $media_name = str_replace(strrchr($media_name, '_'), '', $media_name);

                    $output->writeln('media '.$media_name.' \r\n');
                    // foreach line find the corresponding media
                    $media = $mediaRepository->findOneBy(['name' => $media_name]);
                    if ($media) {
                        // create a transcription for this media
                        $transcription = new Transcription();
                        // re-encode content
                        $transcription->setContent(utf8_encode($xml));
                        // create a transcription log for each transcription
                        $log = new TranscriptionLog();
                        $log->setTranscription($transcription);
                        $log->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                        $log->setUser($user);
                        $log->setName(AppEnums::TRANSCRIPTION_LOG_CREATED);
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
