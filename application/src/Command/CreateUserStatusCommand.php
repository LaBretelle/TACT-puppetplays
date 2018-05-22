<?php

namespace App\Command;

use App\Entity\UserStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserStatusCommand extends Command
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
        ->setName('app:create-user-status')
        ->setDescription('Creates a new user status')
        ->setHelp('This command allows you to create a new user status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Create new user status',
            '===============',
            '',
        ]);

        $helper = $this->getHelper('question');

        $question = new Question('user status name: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the user status name is required');
            }

            return $answer;
        });
        $name = $helper->ask($input, $output, $question);


        $question = new Question('user status description: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the user status description is required');
            }

            return $answer;
        });
        $description = $helper->ask($input, $output, $question);


        $status = new UserStatus();
        $status->setName($name);
        $status->setDescription($description);
        $this->em->persist($status);
        $this->em->flush();

        $output->writeln('ok');
    }
}
