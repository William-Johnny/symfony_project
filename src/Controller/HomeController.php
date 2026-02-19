<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CreateTaskFormType;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function show(EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager
            ->getRepository(Task::class)
            ->findAll();

        if (!$task) {
            throw $this->createNotFoundException(
                'No task found for id '
            );
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tasks' => $task,
        ]);
    }

    #[Route('/entity/create', name: 'entity_create')]
    public function createTask(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => '126@yopmail.com']);

        $task = new Task();
        // $task->setName('thing to do');
        // $task->setDescription("Stuff to do like that and not like this cuz it's important to see this and not that");
        // $task->setLevel('hard');
        // $task->setState('not done');
        // $task->setCreatedBy($user);
        // $task->setCreatedAt(new \DateTimeImmutable('2023-02-11'));

        $form = $this->createForm(CreateTaskFormType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setState('not done');
            $task->setCreatedBy($user);
            $task->setCreatedAt(new \DateTimeImmutable('2023-02-11'));
            $entityManager->persist($task);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/newTask.html.twig', [
            'registrationForm' => $form,
        ]);

        return new Response('Saved new task with id '.$task->getId());
    }
}
