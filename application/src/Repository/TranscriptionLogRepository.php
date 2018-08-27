<?php

namespace App\Repository;

use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TranscriptionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranscriptionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranscriptionLog[]    findAll()
 * @method TranscriptionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscriptionLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TranscriptionLog::class);
    }

    public function getLastLog(Transcription $transcription)
    {
        return $this->createQueryBuilder('tl')
            ->andWhere('tl.transcription = :t')
            ->setParameter('t', $transcription)
            ->orderBy('tl.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastLockLog(Transcription $transcription)
    {
        return $this->createQueryBuilder('tl')
            ->andWhere('tl.transcription = :t')
            ->andWhere('tl.name = :name')
            ->setParameter('t', $transcription)
            ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_LOCKED)
            ->orderBy('tl.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastLogByName(Transcription $transcription, string $name)
    {
        return $this->createQueryBuilder('tl')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name = :name')
          ->setParameter('t', $transcription)
          ->setParameter('name', $name)
          ->orderBy('tl.createdAt', 'DESC')
          ->setMaxResults(1)
          ->getQuery()
          ->getOneOrNullResult();
    }

    public function countValidationLog(Transcription $transcription)
    {
        $qb = $this->createQueryBuilder('tl');
        return $qb->select('COUNT(tl)')
            ->andWhere('tl.transcription = :t')
            ->andWhere('tl.name = :name')
            ->setParameter('t', $transcription)
            ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_VALIDATION_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLogsByUser(Transcription $transcription, User $user)
    {
        return $this->createQueryBuilder('tl')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.transcription = :t')
          ->setParameter('t', $transcription)
          ->setParameter('user', $user)
          ->orderBy('t.createdAt', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult();
    }

//    /**
//     * @return TranscriptionLog[] Returns an array of TranscriptionLog objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TranscriptionLog
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
