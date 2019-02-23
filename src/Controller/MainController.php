<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\UserType;
use App\Form\EventType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function home() {
        return $this->render("home.html.twig");
    }

    /**
     * @Route("/user/list", name="user_list")
     */
    public function userList() : Response
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAll();

        return $this->render("userList.html.twig", ['users' => $users]);
    }

    /**
     * @Route("/user/create", name="user_create")
     * @param $request Request
     * @return Response
     * @throws \Exception
     */
    public function userCreate(Request $request) : Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setDate(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render("userCreate.html.twig", ['form' => $form->createView()]);
    }

    /**
     * @Route("/event/list/{id}", name="event_list")
     * @param $user User
     * @return Response
     */
    public function eventList(User $user) : Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $events = $userRepo->getUserEvents($user);

        return $this->render("eventList.html.twig", ['user' => $user, 'events' => $events]);
    }

    /**
     * @Route("/event/create", name="event_create")
     * @return Response
     * @throws \Exception
     */
    public function eventCreate(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setDate(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }
        return new Response();
    }

    /**
     * @Route("/newOperation", name="new_operation")
     */
    public function newOperation() : Response
    {
        return $this->render("newOperation.html.twig");
    }
}