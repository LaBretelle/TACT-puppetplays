<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findUnarchivedSortedByLog()
    {
        return $this->createQueryBuilder('p')
          ->select('p')
          ->leftJoin('p.medias', 'm')
          ->leftJoin('m.transcription', 't')
          ->leftJoin('t.transcriptionLogs', 'l')
          ->andWhere('p.archived = 0')
          ->addOrderBy('l.createdAt', 'DESC')
          ->addOrderBy('p.id', 'DESC')
          ->getQuery()
          ->getResult();
    }
}
