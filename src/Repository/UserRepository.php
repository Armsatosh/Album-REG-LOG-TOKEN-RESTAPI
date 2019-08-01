<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function fildeUp($jwt ,$currentEmail,$expireTime): ?User
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->andWhere('u.email = :email')
            ->set('u.token'  ,    ':token')
            ->set('u.status' ,    ':status')
            ->set('u.expiretime' ,    ':time')
            ->setParameter('time', 1)
            ->setParameter('status', 1)
            ->setParameter('email', $currentEmail)
            ->setParameter('token' , $jwt)
            ->getQuery()
            ->getOneOrNullResult();

    }



    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
