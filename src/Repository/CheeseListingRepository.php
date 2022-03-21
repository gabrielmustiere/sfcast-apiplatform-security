<?php

namespace App\Repository;

use App\Entity\CheeseListing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheeseListing|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheeseListing|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheeseListing[]    findAll()
 * @method CheeseListing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheeseListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheeseListing::class);
    }
}
