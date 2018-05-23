<?php

namespace App\Command;

use App\Entity\ProjectStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class CreateProjectStatusCommand extends Command
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
        ->setName('app:create-project-status')
        ->setDescription('Creates a new project status')
        ->setHelp('This command allows you to create a new project status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Create new project status',
            '===============',
            '',
        ]);

        $helper = $this->getHelper('question');

        $question = new Question('Project status name: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the project status name is required');
            }

            return $answer;
        });
        $name = $helper->ask($input, $output, $question);


        $question = new Question('Project status description: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the project status description is required');
            }

            return $answer;
        });
        $description = $helper->ask($input, $output, $question);


        $status = new ProjectStatus();
        $status->setName($name);
        $status->setDescription($description);
        $this->em->persist($status);
        $this->em->flush();

        $output->writeln('ok');
    }
}
