<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByQueryString($string)
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->select('u')
            ->andWhere('u.firstname LIKE :val')
            ->orWhere('u.lastname LIKE :val')
            ->orWhere('u.username LIKE :val')
            ->orWhere('u.email LIKE :val')
            ->setParameter('val', '%' . $string . '%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
