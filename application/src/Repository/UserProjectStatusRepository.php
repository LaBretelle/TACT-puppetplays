<?php

namespace App\Repository;

use App\Entity\UserProjectStatus;
use App\Entity\Project;
use App\Service\AppEnums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProjectStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProjectStatus::class);
    }

    public function countManagerByProject(Project $project)
    {
        return $this->createQueryBuilder('ups')
          ->select('count(ups.id)')
          ->join('ups.status', 's')
          ->andWhere('ups.project = :p')
          ->andWhere('s.name = :name')
          ->andWhere('ups.enabled = 1')
          ->setParameter('p', $project)
          ->setParameter('name', AppEnums::USER_STATUS_MANAGER_NAME)
          ->getQuery()
          ->getSingleScalarResult();
    }
}
