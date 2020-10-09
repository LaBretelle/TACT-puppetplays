<?php

namespace App\Twig;

use App\Entity\Project;
use App\Service\FileManager;
use App\Service\MathManager;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProjectExtension extends AbstractExtension
{
    protected $em;
    protected $fm;
    protected $mm;

    public function __construct(EntityManagerInterface $em, FileManager $fm, MathManager $mm)
    {
        $this->em = $em;
        $this->fm = $fm;
        $this->mm = $mm;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('percents', [$this, 'getPercents']),
            new TwigFilter('hasXsl', [$this, 'hasXsl']),
            new TwigFilter('hasScheme', [$this, 'hasScheme']),
        ];
    }

    public function hasXsl(Project $project)
    {
        $xslFile = $this->fm->getProjectPath($project).DIRECTORY_SEPARATOR."export.xsl";

        return file_exists($xslFile);
    }

    public function hasScheme(Project $project)
    {
        $jsonFile = $this->fm->getProjectPath($project).DIRECTORY_SEPARATOR."tei-schema.json";

        return file_exists($jsonFile);
    }


    public function getPercents(Project $project)
    {
        $total = $this->em->getRepository("App:Media")->countByProject($project);

        $validated = (int)$this->em->getRepository("App:Media")->countValidated($project);
        $progress = (int)$this->em->getRepository("App:Media")->countInProgress($project);
        $review = (int)$this->em->getRepository("App:Media")->countInReview($project);
        $none = $total - ($validated + $review + $progress);

        return $this->mm->getPercents($total, $validated, $progress, $review, $none);
    }
}
