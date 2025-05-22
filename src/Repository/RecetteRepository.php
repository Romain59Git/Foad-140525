<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Recherche les recettes dont le nom contient une chaîne donnée.
     *
     * @param string $search
     * @return Recette[]
     */
    public function findByName(string $search): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Recette $recette, bool $flush = false): void
    {
        $this->getEntityManager()->persist($recette);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}