<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function countValidated(Project $project)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->leftJoin('m.transcription', 't')
            ->andWhere('m.project = :project')
            ->andWhere('t.isValid = 1')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countInReview(Project $project)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->leftJoin('m.transcription', 't')
            ->leftJoin('t.reviewRequest', 'r')
            ->andWhere('m.project = :project')
            ->andWhere('r IS NOT NULL')
            ->andWhere('t.isValid = 0 OR t.isValid IS NULL')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countInProgress(Project $project)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->join('m.transcription', 't')
            ->leftJoin('t.reviewRequest', 'r')
            ->andWhere('m.project = :project')
            ->andWhere('t.content != :empty')
            ->andWhere('t.isValid = 0 OR t.isValid IS NULL')
            ->andWhere('r IS NULL')
            ->setParameter('project', $project)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
