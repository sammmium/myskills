<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Task;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends Controller
{
    private $headPage = 'Tasks';

    /**
     * отображение списка задач с кнопками перехода к действиям
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $repositoryTasks = $em->getRepository(Task::class);
        $repositoryTasks->setStatus('active');
        $tasks = $repositoryTasks->getResult();

        /*
         * достаем из сессии id авторизованного пользователя
         */
        $session = new Session();
        $user = unserialize($session->get('_security_main'));
        $idUser = $user->getUser()->getId();

        /*
         * в цикле прогоняем массив записей в таблице task
         */
        if ($tasks) {
            foreach ($tasks as $task) {
                /*
                 * формируем данные строки отображения
                 */
                $dataTask[] = [
                    'taskName' => $task['name'],
                    'userEmail' => $task['email'],
                    'commitLength' => $task['mlc'],
                    'taskId' => $task['id'],
                    'id_user' => $idUser,
                    'author' => $task['author'],
                ];
            }
        } else {
            $dataTask = false;
        }

        /*
         * отрисовываем список задач
         */
        return $this->render('home/index.html.twig', [
            'headPage' => $this->headPage,
            'tasks' => $dataTask,
        ]);
    }
}