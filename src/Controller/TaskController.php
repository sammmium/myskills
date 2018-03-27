<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class TaskController
 * @package App\Controller
 */
class TaskController extends Controller
{
    /**
     * переадресация пользователя на страницу просмотра
     * списка задач с кнопками действия
     *
     * @return Response
     */
    public function indexTask(): Response
    {
        return $this->redirectToRoute('index');
    }

    /**
     * добавление новой задачи
     *
     * @param Request $request
     * @return Response
     */
    public function newTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAuthor($this->getUserId());
            $task->setStatus('new');

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('notice', 'Новая задача добавлена успешно!');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * просмотр выбранной задачи
     *
     * @param Task $task
     * @return Response
     */
    public function showTask(Task $task): Response
    {
        /*
         * достаем данные об авторе задачи
         */
        $emAuthor = $this->getDoctrine()->getRepository(User::class);
        $author = $emAuthor->findOneBy(['id' => $task->getAuthor()]);

        /*
         * достаем данные об авторизованном пользователе
         */
        $session = new Session();
        $user = unserialize($session->get('_security_main'));
        $idUser = $user->getUser()->getId();

        /*
         * дотсаем данные о комментариях к задаче
         */
        $emComments = $this->getDoctrine()->getRepository(Comment::class);
        $emComments->setTaskId($task->getId());
        $comments = $emComments->getResult();

        /*
         * достаем данные об исполнителях задачи
         */
        $executors = $this->getExecutors($task->getExecutors());

        /*
         * отрисовываем страницу просмотра выбранной задачи
         */
        return $this->render('task/show.html.twig', [
            'id' => $task->getId(),
            'id_task' => $task->getId(),
            'task' => $task,
            'executors' => $executors,
            'id_user' => $idUser,
            'author' => [
                'id' => $author->getId(),
                'name' => $author->getUserName(),
                'email' => $author->getEmail(),
            ],
            'comments' => $comments,
        ]);
    }

    /**
     * забираем всех исполнителей задачи
     * по данным из ячейки executors таблицы task,
     * которые по сути являются id пользователей
     *
     * @param $data
     * @return array|bool
     */
    private function getExecutors($data)
    {
        if (empty($data)) {
            return false;
        }

        $result = [];
        $executors = json_decode($data);

        foreach ($executors as $item) {
            $result[] = $this->getDoctrine()->getRepository(User::class)->find($item);
        }
        return $result;
    }

    /**
     * редактирование задачи
     *
     * @param Request $request
     * @param Task $task
     * @return Response
     */
    public function editTask(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        /*
         * достаем данные об авторизованном пользователе
         */
        $session = new Session();
        $user = unserialize($session->get('_security_main'));
        $idUser = $user->getUser()->getId();

        /*
         * достаем данные об авторе задачи
         */
        $emAuthor = $this->getDoctrine()->getRepository(User::class);
        $author = $emAuthor->findOneBy(['id' => $task->getAuthor()]);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * определяем статус задачи как active
             */
            $task->setStatus('active');

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('notice', 'Изменения в задаче сохранены успешно!');
            /*
             * перенаправление на страницу просмотра выбранной
             * задачи для редактирования
             */
            return $this->redirectToRoute('task_show', [
                'id' => $task->getId()
            ]);
        }

        /*
         * достаем данные об исполнителях задачи
         */
        $em = $this->getDoctrine()->getRepository(User::class);
        $executors = $em->findAll();

        /*
         * отрисовка страницы с формой редактирования задачи
         */
        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'executors' => $executors,
            'id_user' => $idUser,
            'author' => [
                'id' => $author->getId(),
                'name' => $author->getUserName(),
                'email' => $author->getEmail(),
            ],
        ]);
    }

    /**
     * удаление задачи
     *
     * @param Request $request
     * @param Task $task
     * @return Response
     */
    public function deleteTask(Request $request, Task $task): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {

            $this->addFlash('notice', 'Произошла ошибка при удалении задачи!');
            return $this->redirectToRoute('task_index');
        }

        /*
         * удаление записи о задаче из сущности и сохранение результата
         */
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('notice', 'Задача удалена успешно!');

        /*
         * перенаправление на страницу просмотра списка задач
         */
        return $this->redirectToRoute('task_index');
    }

    /**
     * получение из сессии данных об авторизованном пользователе
     *
     * @return mixed
     */
    private function getUserId()
    {
        $session = new Session();
        $userSession = unserialize($session->get('_security_main'));
        return $userSession->getUser()->getId();
    }
}
