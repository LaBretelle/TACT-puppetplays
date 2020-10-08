<?php

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function countByAncestors($ancestors)
    {
        return $this->createQueryBuilder('m')
          ->select('count(m.id)')
          ->andWhere('m.parent IN (:ancestors)')
          ->setParameter('ancestors', $ancestors)
          ->getQuery()
          ->getSingleScalarResult();
    }

    public function countValidated(Project $project, $ancestors = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->leftJoin('m.transcription', 't')
            ->andWhere('m.project = :project')
            ->andWhere('t.isValid = 1')
            ->setParameter('project', $project);

        if ($ancestors) {
            $qb->andWhere('m.parent IN (:ancestors)')
               ->setParameter('ancestors', $ancestors);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function countInReview(Project $project, $ancestors = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->leftJoin('m.transcription', 't')
            ->leftJoin('t.reviewRequest', 'r')
            ->andWhere('m.project = :project')
            ->andWhere('r IS NOT NULL')
            ->andWhere('t.isValid = 0 OR t.isValid IS NULL')
            ->setParameter('project', $project);

        if ($ancestors) {
            $qb->andWhere('m.parent IN (:ancestors)')
               ->setParameter('ancestors', $ancestors);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countInProgress(Project $project, $ancestors = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->join('m.transcription', 't')
            ->leftJoin('t.reviewRequest', 'r')
            ->andWhere('m.project = :project')
            ->andWhere('t.content != :empty')
            ->andWhere('t.isValid = 0 OR t.isValid IS NULL')
            ->andWhere('r IS NULL')
            ->setParameter('project', $project)
            ->setParameter('empty', '');

        if ($ancestors) {
            $qb->andWhere('m.parent IN (:ancestors)')
               ->setParameter('ancestors', $ancestors);
        }

        return $qb
                ->getQuery()
                ->getSingleScalarResult();
    }

    public function getByProjectAndUserActivity(Project $project, Directory $parent = null, User $user, $allMedia)
    {
        $names = ['transcription_log_updated', 'transcription_log_waiting_for_validation', 'transcription_log_rereaded'];

        $qb = $this->createQueryBuilder('m')
          ->leftjoin('m.transcription', 't')
          ->leftjoin('t.transcriptionLogs', 'tl')
          ->andWhere('m.project = :project')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.name IN (:names)')
          ->setParameter('user', $user)
          ->setParameter('project', $project)
          ->groupBy('m.id')
          ->setParameter('names', $names)
          ->orderBy('tl.createdAt', 'DESC');

        if (!$allMedia) {
            if (!$parent) {
                $qb->andWhere('m.parent IS NULL');
            } else {
                $qb->andWhere('m.parent = :parent')->setParameter('parent', $parent);
            }
        }

        return $qb->getQuery()->getResult();
    }


    public function getByProjectAndLocked(Project $project, Directory $parent = null, User $user, $allMedia)
    {
        $now = new \DateTime;
        $nowMinus2 =  new \DateTime;
        $nowMinus2->modify('-2 minutes');

        $qb = $this->createQueryBuilder('m')
        ->leftjoin('m.transcription', 't')
        ->leftjoin('t.transcriptionLogs', 'tl')
        ->andWhere('m.project = :project')
        ->andWhere('tl.user != :user')
        ->andWhere('tl.name = :name')
        ->andWhere('tl.createdAt BETWEEN :dateMinus2 AND :date')
        ->setParameter('user', $user)
        ->setParameter('project', $project)
        ->setParameter('date', $now)
        ->setParameter('dateMinus2', $nowMinus2)
        ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_LOCKED);

        if (!$allMedia) {
            if (!$parent) {
                $qb->andWhere('m.parent IS NULL');
            } else {
                $qb->andWhere('m.parent = :parent')->setParameter('parent', $parent);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
