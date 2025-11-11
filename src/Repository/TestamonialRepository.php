<?php

namespace App\Repository;

use App\Entity\Testamonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testamonial>
 */
class TestamonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testamonial::class);
    }

    public function findAllOrdered(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('t')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllSearch($search)
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Testamonial[] Returns an array of Testamonial objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Testamonial
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
