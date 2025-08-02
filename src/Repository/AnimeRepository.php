<?php

namespace App\Repository;

use App\Entity\Anime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Anime>
 */
class AnimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anime::class);
    }

    public function save(Anime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Anime $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Nouvelle méthode pour trouver les animés par tags
    public function findByTags(array $tagIds): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.genres', 'g')
            ->where('g.id IN (:tags)')
            ->setParameter('tags', $tagIds)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}