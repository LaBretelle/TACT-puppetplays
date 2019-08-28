<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Entity\Project;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;

class ProjectExtension extends AbstractExtension
{
    protected $em;
    protected $fm;

    public function __construct(EntityManagerInterface $em, FileManager $fm)
    {
        $this->em = $em;
        $this->fm = $fm;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('percents', array($this, 'getPercents')),
            new TwigFilter('hasXsl', array($this, 'hasXsl')),
            new TwigFilter('hasScheme', array($this, 'hasScheme')),
        );
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
