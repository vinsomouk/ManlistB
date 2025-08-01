<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findByQuestionnaireOrdered(int $questionnaireId): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.questionnaire = :questionnaireId')
            ->setParameter('questionnaireId', $questionnaireId)
            ->orderBy('q.order', 'ASC')
            ->getQuery()
            ->getResult();
    }
}