<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllUsersWithPaginationAndClient(int $page,int $limit, ?int $clientId): array
    {
        $qb = $this->createQueryBuilder('b')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        if($clientId !== null){
            $qb->where('b.client = :clientId')
            ->setParameter('clientId', $clientId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByClient(EntityManager $entityManager, int $clientId): array
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from('Utilisateur', 'u')
            ->join('u.client', 'c')
            ->where($queryBuilder->expr()->eq('c.id', ':clientId'))
            ->setParameter('clientId', $clientId);

        return $queryBuilder->getQuery()->getResult();
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


}
