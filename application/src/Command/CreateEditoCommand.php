<?php

namespace App\Command;

use App\Entity\EditorialContent;
use App\Service\EditorialContentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateEditoCommand extends Command
{
    private $em;
    private $editorialContentManager;
    protected static $defaultName = 'app:create-edito';

    public function __construct(EntityManagerInterface $em, EditorialContentManager $editorialContentManager)
    {
        $this->editorialContentManager = $editorialContentManager;
        $this->em = $em;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('create editorial content');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $names = ["actualités"];
        foreach ($names as $name) {
            if (!$this->em->getRepository(EditorialContent::class)->findOneByName($name)) {
                $this->editorialContentManager->create($name);
                $io->success("create ".$name);
            }
        }

        return;
    }
}
