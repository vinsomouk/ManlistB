<?php

namespace App\Repository;

use App\Entity\AnswerOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnswerOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnswerOption::class);
    }

    public function findByQuestion(int $questionId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}