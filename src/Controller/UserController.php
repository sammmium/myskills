<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends Controller
{
    /**
     * добавление нового пользователя (исполнителя)
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function newExecutor(Request $request, $id): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        /*
         * не знаю почем, но проверка данных не работает, возможно
         * потому что форма не была полностью сгенерирована через
         * класс TaskType, так как часть ее пришлось дописать самому?!
         */
        if ($form->isSubmitted() /*&& $form->isValid()*/) {
            /*
             * получаем id добавляемого исполнителя с формы
             */
            $post = $request->get('user');
            $idExecutor = $post['executor'];

            /*
             * вытаскиваем json строку с массивом id исполнителей задачи
             */
            $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $id]);
            $executorsAsIs = $task->getExecutors();

            /*
             * проводим проверку на наличие данных в строке
             * дополняем массив новым id исполнителя
             * снова преобразуем массив в json строку
             */
            if (!empty($executorsAsIs)) {
                $executors = json_decode($executorsAsIs);
                if (in_array($idExecutor, $executors)) {
                    $executors = json_encode($executors);
                } else {
                    $executors = json_encode(array_merge($executors, [$idExecutor]));
                }
            } else {
                $executors = json_encode([$idExecutor]);
            }

            /*
             * добавляем подготовленную строку в сущность
             */
            $task->setExecutors($executors);

            /*
             * сохраняем подготовленные данные
             */
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('notice', 'Исполнитель задачи добавлен успешно!');

            /*
             * переходим к задаче, где только что был добавлен новый исполнитель
             */
            return $this->redirectToRoute('task_show', ['id' => $id]);
        }

        /*
         * отрисовываем форму добавления нового испольнителя
         */
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'executors' => $user,
            'id_task' => $id
        ]);
    }

    /**
     * удаление id исполнителя из массива id исполнителей,
     * добавленных к выбранной задаче
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function deleteExecutor(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('task_index');
        }

        /*
         * id задачи передан через форму удаления исполнителя
         */
        $postIdTask = $request->get('id_task');

        $idExecutor = $user->getId();

        /*
         * получаем json строку, в которой находится массив с id исполнителей задачи
         */
        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $postIdTask]);
        $executorsAsIs = $task->getExecutors();

        /*
         * преобразуем json строку в массив,
         * ищем и удаляем совпадение id исполнителя задачи
         */
        $executors = json_decode($executorsAsIs);
        if (in_array($idExecutor, $executors)) {
            $executors = json_encode($this->excludeExecutor($idExecutor, $executors));
        } else {
            $executors = json_encode($executors);
        }

        /*
         * передаем для дальнейшей записи подготовленную json строку с массивом
         * оставшихся исполнителей в сущность task и записываем данные в таблицу
         */
        $task->setExecutors($executors);
        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        $this->addFlash('notice', 'Исполнитель задачи удален успешно!');

        /*
         * переходим на страницу просмотра задачи, где удалялся исполнитель
         * на странице, которая будет отображена на экране, удаленного
         * исполнителя уже не будет
         */
        return $this->redirectToRoute('task_show', ['id' => $postIdTask]);
    }

    /**
     * исключаем из массива пользователей (исполнителей),
     * у которых id совпал с переданным в аргументе id
     * исключаемого исполнителя
     *
     *
     * @param $idExecutor
     * @param $executors
     * @return array
     */
    private function excludeExecutor($idExecutor, $executors)
    {
        $result = [];
        foreach ($executors as $executor) {
            if ((integer) $executor !== (integer) $idExecutor) {
                $result[] = $executor;
            }
        }
        return $result;
    }
}
