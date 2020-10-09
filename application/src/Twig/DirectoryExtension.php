<?php

namespace App\Twig;

use App\Entity\Directory;
use App\Service\DirectoryManager;
use App\Service\MathManager;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DirectoryExtension extends AbstractExtension
{
    protected $em;
    protected $dm;
    protected $mm;

    public function __construct(EntityManagerInterface $em, DirectoryManager $dm, MathManager $mm)
    {
        $this->em = $em;
        $this->dm = $dm;
        $this->mm = $mm;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('directoryPercents', [$this, 'getPercents'])
        ];
    }

    public function getPercents(Directory $directory)
    {
        $project = $directory->getProject();
        $ancestors = $this->dm->getSubordinates($directory);

        $total = $this->em->getRepository("App:Media")->countByAncestors($ancestors);
        $validated = (int)$this->em->getRepository("App:Media")->countValidated($project, $ancestors);
        $progress = (int)$this->em->getRepository("App:Media")->countInProgress($project, $ancestors);
        $review = (int)$this->em->getRepository("App:Media")->countInReview($project, $ancestors);
        $none = $total - ($validated + $review + $progress);

        return $this->mm->getPercents($total, $validated, $progress, $review, $none);
    }
}
