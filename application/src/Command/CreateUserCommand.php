<?php

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:create-user')
        ->setDescription('Creates a new user.')
        ->setHelp('This command allows you to create a user.')
        ->addOption(
            'admin',
            'a',
            InputOption::VALUE_NONE,
            'Should we create an admin user?',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Create new User',
            '===============',
            '',
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Please follow the instructions carefully.');


        $helper = $this->getHelper('question');
        // username
        $question = new Question('Enter the name of the user: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('The name of the user is required');
            }

            return $answer;
        });
        $question->setMaxAttempts(2);
        $username = $helper->ask($input, $output, $question);

        // mail
        $question = new Question('Enter the mail of the user: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('The mail of the user is required');
            }

            return $answer;
        });
        $question->setMaxAttempts(2);
        $email = $helper->ask($input, $output, $question);

        // password
        $question = new Question('Enter user password: ');
        $question->setValidator(function ($value) {
            if (trim($value) === '') {
                throw new \Exception('The password cannot be empty');
            }

            return $value;
        });
        $question->setHidden(true);
        $question->setMaxAttempts(2);
        $password = $helper->ask($input, $output, $question);

        $roles = ['ROLE_USER'];

        if ($input->getOption('admin') === true) {
            $roles[] = 'ROLE_ADMIN';
        }

        $this->userManager->createUser($username, $email, $password, $roles);

        $output->writeln('User ' . $username . ' created \o/');
    }
}
