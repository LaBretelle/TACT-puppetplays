<?php

namespace App\Service;

use App\Entity\IiifServer;
use App\Entity\Project;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;

class IiifManager
{
    protected $em;
    protected $fm;

    public function __construct(
        EntityManagerInterface $em,
        FlashManager $fm
    ) {
        $this->em = $em;
        $this->fm = $fm;
    }

    public function create(Project $project)
    {
        $server = new IiifServer;
        $server->setProject($project);

        return $server;
    }

    public function save(IiifServer $server)
    {
        $this->em->persist($server);
        $this->em->flush();

        $this->fm->add('notice', 'Serveur sauvegardé');

        return;
    }

    public function delete(IiifServer $server)
    {
        $mediaCount = count($server->getMedias());
        if ($mediaCount == 0) {
            $this->em->remove($server);
            $this->em->flush();
            $this->fm->add('notice', 'Serveur supprimé');
        } else {
            $this->fm->add('notice', 'Serveur non supprimé. Des médias ('.$mediaCount.') sont encore liés à ce serveur.');
        }

        return;
    }
}
