<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
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
     */
    public function userCreate()
    {
        return $this->render("userCreate.html.twig");
    }

    /**
     * @Route("/event/list/{user}", name="event_list")
     */
    public function eventList(User $user) : Response
    {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = array(); // TODO lister les events et afficher un liens de création d'event

        return $this->render("eventList.html.twig");
    }

    /**
     * @Route("/newOperation", name="new_operation")
     */
    public function newOperation() : Response
    {
        return $this->render("newOperation.html.twig");
    }
}