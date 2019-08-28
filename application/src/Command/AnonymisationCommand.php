<?php

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AnonymisationCommand extends Command
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
        ->setName('app:anonymise')
        ->setDescription('anonymise users')
        ->setHelp('This command allows you to create a user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>anonymise ghost users</info>',
            '...',
        ]);

        $this->userManager->anonymiseUsers();

        $output->writeln([
            '<info>anonymisation done</info>',
        ]);

        return;
    }
}
