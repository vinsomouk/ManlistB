<?php

namespace App\Repository;

use App\Entity\UserResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResponse::class);
    }

    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.questionnaire', 'q')
            ->addSelect('q')
            ->where('r.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
}