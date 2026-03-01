<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CreateTaskFormType;
use App\Form\AssignFormType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;
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

    #[Route('/detail/{id}', name: 'task_detail')]
    public function showTaskDetail(Task $task, EntityManagerInterface $entityManager, #[CurrentUser] User $user, Request $request): Response
    {
        $subtask = new Task();
        $form = $this->createForm(CreateTaskFormType::class, $subtask);
        $userRepository = $entityManager
            ->getRepository(User::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subtask->setState('not done');
            $subtask->setCreatedBy($user);
            $subtask->setCreatedAt(new \DateTimeImmutable());
            $task->addChildTask($subtask);
            $entityManager->persist($subtask);
            $entityManager->flush();

            return $this->redirectToRoute('task_detail',['id' => $task->getId()]);
        }

        return $this->render('task/taskDetail.html.twig', [
            'task' => $task,
            'users' => $userRepository->findAll(),
            'subtaskForm' => $form,
        ]);
    }

    #[Route('/task/{id}/assign', name: 'task_assign', methods: ['POST'])]
    public function assign(
        Task $task,
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid(
            'assign_task_' . $task->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        function getBonus($taskArray) : int {
        $bonus=0;

        for ($i=0; $i < count($taskArray); $i++) {
            if ($taskArray[$i]->getLevel() == "hard") {
                $bonus+=2;
            }else if ($taskArray[$i]->getLevel() == "medium"){
                $bonus+=1;
            }
        }

        return $bonus;
    }

        $assigneeId = $request->request->get('assignee_id');

        $userRepository = $em
            ->getRepository(User::class);

        if ($assigneeId) {
            $user = $userRepository->find($assigneeId);

            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('task_show', [
                    'id' => $task->getId()
                ]);
            }

            $taskArray = $em
                ->getRepository(Task::class)
                ->findAll();

            $userArray = $em
                ->getRepository(User::class)
                ->findAll();

            if ($user->getAssignedTasks()->count() + getBonus($user->getAssignedTasks()) < (count($taskArray) + getBonus($taskArray)) / count($userArray)) {
                $this->addFlash('error', $user->getAssignedTasks()->count());
                $task->setAssignedTo($user);
            }else{
                $this->addFlash('error', 'This user has to much to do !');
                return $this->redirectToRoute('task_assign', [
                    'id' => $task->getId()
                ]);
            }
        } else {
            // Unassign if empty value selected
            $task->setAssignedTo(null);
        }

        $em->flush();

        $this->addFlash('success', 'Task assignment updated.');

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
