<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ExecutorController
 * @package App\Controller
 */
class ExecutorController extends Controller
{
    /**
     * добавление нового исполнителя
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addExecutor(Request $request, $id)
    {

        $task = new User();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAuthor($this->getUserId());
            $task->setStatus('active');

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('notice', 'Исполнитель задачи добавлен успешно!');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }
}