<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectExtension extends AbstractExtension
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('percents', array($this, 'getPercents')),
        );
    }

    public function getPercents(Project $project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        $validated = (int)$this->em->getRepository("App:Media")->countValidated($project);
        $progress = (int)$this->em->getRepository("App:Media")->countInProgress($project);
        $review = (int)$this->em->getRepository("App:Media")->countInReview($project);
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
