<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/*
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    private $status = false;

    public function setStatus($data)
    {
        $this->status = $data;
        return;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    private function createObjects()
    {
        if ($this->status == 'active') {
            return $this->createQueryParameters();
        }
        return $this->findAll();
    }

    /**
     * подготовка параметров запроса и их передача
     * в метод получения массива объектов
     *
     * @return mixed
     */
    private function createQueryParameters()
    {
        $parameters = [
            'statusNew' => 'new',
            'statusActive' => 'active'
        ];
        return $this->getResultByQueryParameters($parameters);
    }

    /**
     * построение запроса и запрос на выборку данных
     *
     * @param $parameters
     * @return mixed
     */
    private function getResultByQueryParameters($parameters)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'select t1.id, t1.name, t2.author, t3.email, max(length(t2.text)) as mlc from App\Entity\Task t1 left join App\Entity\Comment t2 with t1.id = t2.task left join App\Entity\User t3 with t2.author = t3.id where t1.status = :statusNew or t1.status = :statusActive group by t1.id order by t1.id asc'
        )->setParameters($parameters);

        return $query->execute();
    }

    /**
     * получение результата запроса к БД
     *
     * @return array|bool|mixed
     */
    public function getResult()
    {
        $objects = $this->createObjects();

        if (!$objects) {
            return false;
        }

        return $objects;
    }
}
