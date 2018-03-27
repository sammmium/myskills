<?php

namespace App\Repository;

use App\Entity\Executor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExecutorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Executor::class);
    }

    /**
     * получение списка исполнителей задачи
     * для выпадающего списка
     *
     * @return mixed
     */
    public function getExecutors()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'select u.id, u.username, u.email
                from App\Entity\User u
                order by u.username asc'
        );
        return $query->execute();
    }
}