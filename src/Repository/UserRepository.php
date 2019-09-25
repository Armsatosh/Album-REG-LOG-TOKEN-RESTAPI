<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use phpDocumentor\Reflection\Types\Object_;
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

    public function filideUp($jwt, $currentEmail, $expireTime): ? int
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->andWhere('u.email =           :email')
            ->set('u.token'  ,              ':token')
            ->set('u.status' ,              ':status')
            ->set('u.expire' ,              ':limit')
            ->setParameter('limit',     $expireTime)
            ->setParameter('status',    1)
            ->setParameter('email',     $currentEmail)
            ->setParameter('token' ,    $jwt)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findUserByToken($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.token = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNameByEmail($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.email = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeToken($jwt): ? int
    {
        return $this->createQueryBuilder('t')
            ->update()
            ->andWhere('t.token =  :token')
            ->set('t.token'  ,    ':reToken')
            ->set('t.expire' ,    ':expire')
            ->setParameter('token',     $jwt)
            ->setParameter('reToken' ,    "")
            ->setParameter('expire' ,    "")
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
