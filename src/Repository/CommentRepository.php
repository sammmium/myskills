<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    private $taskId = false;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * запись в свойство id задачи
     *
     * @param $data
     */
    public function setTaskId($data)
    {
        $this->taskId = $data;
        return;
    }

    /**
     * создание массива объектов комментариев,
     * выбранных по id задачи
     *
     * @return mixed
     */
    private function createObjects()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'select c1.text, u1.email
                from App\Entity\Comment c1
                left join App\Entity\User u1 with c1.author = u1.id
                where c1.task = :taskId'
        )->setParameters(['taskId' => $this->taskId]);

        return $query->execute();
    }

    /**
     * получение результата выборки комментариев
     *
     * @return bool|mixed
     */
    public function getResult()
    {
        $objects = $this->createObjects();

        return !$objects ? false : $objects;
    }
}
