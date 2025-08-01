<?php

namespace App\Repository;

use App\Entity\Questionnaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuestionnaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaire::class);
    }

    public function findActiveQuestionnaires(): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('q.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}