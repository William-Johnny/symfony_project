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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function show(EntityManagerInterface $entityManager, #[CurrentUser] User $user): Response
    {
        $task = $entityManager
            ->getRepository(Task::class)
            ->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tasks' => $task,
            'username' => $user->getUsername(),
        ]);
    }

    #[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/entity/create', name: 'entity_create')]
    public function createTask(Request $request, EntityManagerInterface $entityManager, #[CurrentUser] User $user): Response
    {

        $task = new Task();

        $form = $this->createForm(CreateTaskFormType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setState('not done');
            $task->setCreatedBy($user);
            $task->setCreatedAt(new \DateTimeImmutable('2023-02-11'));
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/newTask.html.twig', [
            'registrationForm' => $form,
        ]);

        return new Response('Saved new task with id '.$task->getId());
    }
}
