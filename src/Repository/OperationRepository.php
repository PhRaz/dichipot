<?php

namespace App\Repository;

use App\Entity\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * @param $operationId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findForUpdate($operationId)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.expenses', 'oe')
            ->innerJoin('oe.user', 'oeu')
            ->innerJoin('o.payments', 'op')
            ->innerJoin('op.user', 'opu')
            ->addSelect('oe')
            ->addSelect('oeu')
            ->addSelect('op')
            ->addSelect('opu')
            ->andWhere('o.id = :operationId')
            ->setParameter('operationId', $operationId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
