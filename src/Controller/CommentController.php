<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommentController extends Controller
{
    /**
     * добавление комментария к задаче
     * в агументе передается id задачи и id автора задачи
     *
     * @param Request $request
     * @param $id_task
     * @param $id_user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addYourComment(Request $request, $id_task, $id_user)
    {
        $post = $request->get('add_comment');

        if ($post) {
            /*
             * проверку не добавлял ввиду отсутствия времени
             */
            if (empty($post['comment'])) {
                $this->addFlash('notice', 'Для сохранения комментирия необходимо заполнить соответствующее поле!');
            } else {
                /*
                 * проверка на наличие комментария от пользователя
                 * именно к данной задаче
                 * при наличии совпадения комментарий добавлен не будет
                 */
                if (!$this->isMatchComment($id_task, $id_user)) {
                    $comment = new Comment();
                    $comment->setText($post['comment']);
                    $comment->setDate(new \DateTime('now'));
                    $comment->setAuthor($id_user);
                    $comment->setTask($id_task);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($comment);
                    $em->flush();

                    $this->addFlash('notice', 'Ваш комментарий был добавлен успешно!');
                } else {
                    $this->addFlash('notice', 'Ваш комментирий добавлен не был! Пользователь может оставить только один комментирий к задаче!');
                }
            }
        }
        return $this->redirectToRoute('task_show', [
            'id' => $id_task
        ]);
    }

    private function isMatchComment($id_task, $id_user)
    {
        $emComment = $this->getDoctrine()->getRepository(Comment::class);
        $comment = $emComment->findBy(['task' => $id_task, 'author' => $id_user]);
        return $comment ? true : false;
    }
}
