<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Entity\Directory;
use App\Service\DirectoryManager;
use Doctrine\ORM\EntityManagerInterface;

class DirectoryExtension extends AbstractExtension
{
    protected $em;
    protected $dm;

    public function __construct(EntityManagerInterface $em, DirectoryManager $dm)
    {
        $this->em = $em;
        $this->dm = $dm;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('directoryPercents', array($this, 'getPercents'))
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

        $validatedPercent = ($validated != 0)
          ? $validated/$total*100
          : 0;

        $progressPercent = ($progress != 0)
          ? $progress/$total*100
          : 0;

        $reviewPercent = ($review != 0)
          ? $review/$total*100
          : 0;

        $nonePercent = ($none != 0)
          ? $none/$total*100
          : 0;

        return [
          $validatedPercent,
          $progressPercent,
          $reviewPercent,
          $nonePercent
        ];
    }
}
