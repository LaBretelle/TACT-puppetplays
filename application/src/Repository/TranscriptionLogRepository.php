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

    public function countValidationLog(Transcription $transcription, TranscriptionLog $lastAskForValidationLog = null)
    {
        $qb = $this->createQueryBuilder('tl');
        $qb->select('COUNT(tl)')
            ->andWhere('tl.transcription = :t')
            ->andWhere('tl.name = :name')
            ->setParameter('t', $transcription)
            ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_VALIDATION_PENDING);
        if (null !== $lastAskForValidationLog) {
            $qb->andWhere('tl.createdAt >= :date')->setParameter('date', $lastAskForValidationLog->getCreatedAt());
        }
        return $qb->getQuery()->getSingleScalarResult();
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

    public function getLogs(Transcription $transcription)
    {
        return $this->createQueryBuilder('tl')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name NOT IN(:exceptions)')
          ->setParameter('t', $transcription)
          ->setParameter('exceptions', 'transcription_log_locked')
          ->orderBy('tl.createdAt', 'ASC')
          ->getQuery()
          ->getResult();
    }

    public function userHasTranscription(Transcription $transcription, User $user)
    {
        $results = $this->createQueryBuilder('tl')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name IN(:names)')
          ->setParameter('t', $transcription)
          ->setParameter('user', $user)
          ->setParameter('names', 'transcription_log_locked, transcription_log_created, transcription_log_waiting_for_validation, transcription_log_rereaded')
          ->orderBy('tl.createdAt', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult();

        return count($results) > 0;
    }

    public function userHasValidation(Transcription $transcription, User $user)
    {
        $results = $this->createQueryBuilder('tl')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name IN(:names)')
          ->setParameter('t', $transcription)
          ->setParameter('user', $user)
          ->setParameter('names', 'transcription_log_validated')
          ->orderBy('tl.createdAt', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult();

        return count($results) > 0;
    }
}
