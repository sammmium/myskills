<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WelcomeController extends Controller
{
    public function index()
    {
        $contentHead = 'Приветствие';
        $content = "Мы рады приветствовать Вас на тестовом ресурсе просмотра задач!!!";

        return $this->render('default/index.html.twig', [
            'contentHead' => $contentHead,
            'contentBody' => $content,
        ]);
    }
}