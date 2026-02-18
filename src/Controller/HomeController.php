<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    // public function index(): Response
    // {
    //     $task = new Task();
    //     return $this->render('home/index.html.twig', [
    //         'controller_name' => 'HomeController',
    //         'tasks' => [
    //             [
    //                 'name' => 'small thing to do',
    //                 'description' => "Stuff to do like that and not like this cuz it's important to see this and not that",
    //                 'level' => 'easy',
    //                 'author' => 'Decima',
    //                 'date' => new \DateTime('2023-02-11'),
    //             ],
    //             [
    //                 'name' => 'hard thing to do',
    //                 'description' => "Stuff to do like that and not like this cuz it's important to see this and not that",
    //                 'level' => 'hard',
    //                 'author' => 'Decima',
    //                 'date' => new \DateTime('2023-02-11'),
    //             ],
    //             [
    //                 'name' => $task->getName(),
    //                 'description' => $task->getDescription(),
    //                 'level' => $task->getLevel(),
    //                 'author' => $task->getCreatedBy(),
    //                 'date' => $task->getCreatedAt(),
    //             ],
    //         ],
    //     ]);
    // }
    public function show(EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager
            ->getRepository(Task::class)
            ->findAll();

        if (!$task) {
            throw $this->createNotFoundException(
                'No product found for id '
            );
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tasks' => $task,
        ]);
    }

    #[Route('/entity/create', name: 'entity_create')]
    public function createTask(EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setUsername('John');
        $user->setEmail('124@yopmail.com');
        $user->setRoles(['tester']);
        $user->setPassword('tester');

        $product = new Task();
        $product->setName('thing to do');
        $product->setDescription("Stuff to do like that and not like this cuz it's important to see this and not that");
        $product->setLevel('hard');
        $product->setState('not done');
        $product->setCreatedBy($user);
        $product->setCreatedAt(new \DateTimeImmutable('2023-02-11'));

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);
        $entityManager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }
}
