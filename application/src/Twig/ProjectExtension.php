<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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
            new TwigFilter('validationPercent', array($this, 'validationPercent')),
            new TwigFilter('reviewPercent', array($this, 'reviewPercent')),
            new TwigFilter('nonePercent', array($this, 'nonePercent')),
            new TwigFilter('progressPercent', array($this, 'progressPercent')),
        );
    }

    public function validationPercent($project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        if ($validated = (int)$this->em->getRepository("App:Media")->countValidated($project)) {
            return $validated/$total*100;
        }

        return 0;
    }

    public function reviewPercent($project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        if ($validated = (int)$this->em->getRepository("App:Media")->countInReview($project)) {
            return $validated/$total*100;
        }

        return 0;
    }

    public function progressPercent($project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        if ($validated = (int)$this->em->getRepository("App:Media")->countInProgress($project)) {
            return $validated/$total*100;
        }

        return 0;
    }

    public function nonePercent($project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        if ($validated = (int)$this->em->getRepository("App:Media")->countNoTranscription($project)) {
            return $validated/$total*100;
        }

        return 0;
    }
}
