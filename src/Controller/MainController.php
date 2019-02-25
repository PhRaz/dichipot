<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Form\UserType;
use App\Form\EventType;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @route("/", name="home")
     */
    public function home() {
        return $this->render("home.html.twig");
    }

    /**
     * @route("/user/list", name="user_list")
     */
    public function userList() : Response
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAll();
        return $this->render("userList.html.twig", ['users' => $users]);
    }

    /**
     * @route("/user/create", name="user_create")
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
     * @route("/event/list/{userId}", name="event_list")
     * @param $userId
     * @return Response
     */
    public function eventList($userId) : Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->getUserEvents($userId);
        return $this->render("eventList.html.twig", ['user' => $user[0]]);
    }

    /**
     * @route("/event/create/{id}", name="event_create")
     * @param $request Request
     * @param $user User
     * @return Response
     * @throws \Exception
     */
    public function eventCreate(Request $request, User $user)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $userEvent = new UserEvent();
            $userEvent->setDate(new \DateTime());
            $userEvent->setAdministrator(true);
            $event->setDate(new \DateTime());

            $userEvent->setUser($user);
            $userEvent->setEvent($event);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->persist($userEvent);
            $entityManager->flush();

            return $this->redirectToRoute('event_list', ['userId' => $user->getId()]);
        }

        return $this->render('eventCreate.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @route("/operation/list/{eventId}", name="operation_list")
     */
    public function operationList($eventId)
    {
        /** @var EventRepository $eventRepo */
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $event = $eventRepo->getEventOperations($eventId);

        return $this->render('operationList.html.twig', ['event' => $event[0]]);
    }

    /**
     * @Route("/operation/create", name="operation_create")
     */
    public function operationCreate() : Response
    {
        return $this->render("operationCreate.html.twig");
    }
}