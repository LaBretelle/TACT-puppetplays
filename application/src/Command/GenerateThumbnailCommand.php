<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class GenerateThumbnailCommand extends Command
{
    private $em;
    private $fileManager;


    public function __construct(EntityManagerInterface $em, FileManager $fileManager, ProjectManager $projectManager)
    {
        $this->em = $em;
        $this->fileManager = $fileManager;
        $this->pm = $projectManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:generate-thumbnails')
        ->setDescription('Generate thumbnails')
        ->setHelp('generatae thumbnails for a given project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            'Generate thumbnails',
            '======================================================',
            '',
        ]);

        $projectRepository = $this->em->getRepository(Project::class);

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

        $question = new Question('Thumbnail width: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('Thumbnail width is required');
            }

            return $answer;
        });
        $width = intval($helper->ask($input, $output, $question));


        $medias = $project->getMedias();
        $projectFilesPath = $this->fileManager->getProjectPath($project);

        $thumbnailDir = $projectFilesPath.DIRECTORY_SEPARATOR.'thumbnails';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir);
        }

        foreach ($medias as $media) {
            $this->pm->generateThumbnail($projectFilesPath, $media->getUrl(), $width);
        }
    }
}
