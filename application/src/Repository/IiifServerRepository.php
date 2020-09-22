<?php

namespace App\Repository;

use App\Entity\IiifServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IiifServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IiifServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IiifServer[]    findAll()
 * @method IiifServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IiifServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IiifServer::class);
    }
}
