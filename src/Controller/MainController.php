<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Expense;
use App\Entity\Payment;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Form\OperationType;
use App\Form\UserType;
use App\Form\EventType;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @route("/", name="home")
     */
    public function home()
    {

        return $this->render("home.html.twig");
    }

    /**
     * @route("/user/list", name="user_list")
     */
    public function userList(): Response
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
    public function userCreate(Request $request): Response
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
    public function eventList($userId): Response
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

            $event->setDate(new \DateTime());

            $userEvent = new UserEvent();
            $userEvent->setDate(new \DateTime());
            $userEvent->setAdministrator(true);
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
     * @route("/event/addUser/{eventId}/{userId}", name="event_add_user")
     * @param Request $request
     * @param $eventId
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function eventAddUser(Request $request, $eventId, $userId)
    {
        /** @var Event $event */
        $event = $this->getDoctrine()->getRepository(Event::class)->find($eventId);
        /** @var User $administrator */
        $administrator = $this->getDoctrine()->getRepository(User::class)->find($userId);

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setDate(new \DateTime());

            $userEvent = new UserEvent();
            $userEvent->setDate(new \DateTime());
            $userEvent->setAdministrator(false);

            $userEvent->setUser($user);
            $userEvent->setEvent($event);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->persist($userEvent);
            $entityManager->flush();

            return ($this->redirectToRoute('event_list', ['userId' => $administrator->getId()]));
        }

        return $this->render('eventAddUser.html.twig', ['form' => $form->createView(), 'event' => $event, 'administrator' => $administrator]);
    }

    /**
     * @route("/operation/list/{eventId}", name="operation_list")
     */
    public function operationList($eventId)
    {
        /** @var EventRepository $eventRepo */
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $event = $eventRepo->getEventOperations($eventId);

        /*
         * compute balance
         */
        $balance = array();
        $grandTotal = 0;
        foreach ($event[0]->getOperations() as $operation) {
            $id = $operation->getId();

            $totalExpense = 0;
            foreach ($operation->getExpenses() as $expense) {
                $grandTotal += $expense->getAmount();
                $totalExpense += $expense->getAmount();
                $balance[$id][$expense->getUser()->getName()]['expense'] = $expense->getAmount();
            }

            $totalPayment = 0;
            foreach ($operation->getPayments() as $payment) {
                $totalPayment += $payment->getAmount();
                $balance[$id][$payment->getUser()->getName()]['payment'] = $payment->getAmount();
            }

            foreach ($balance[$id] as $userName => $data) {
                $amountToPay = $totalExpense / $totalPayment * $balance[$id][$userName]['payment'];
                $balance[$id][$userName] = array_merge($balance[$id][$userName], [
                    'amountToPay' => $amountToPay,
                    'balance' => $balance[$id][$userName]['expense'] - $amountToPay
                ]);
            }
        }

        $total = array();
        foreach ($balance as $id => $balanceData) {
            foreach ($balanceData as $userName => $userData) {
                if (! isset($total[$userName])) {
                    $total[$userName] = [
                        'expense' => 0,
                        'payment' => 0,
                        'amountToPay' => 0,
                        'balance' => 0
                    ];
                }
                $total[$userName]['expense'] += $userData['expense'];
                $total[$userName]['payment'] += $userData['payment'];
                $total[$userName]['amountToPay'] += $userData['amountToPay'];
                $total[$userName]['balance'] += $userData['balance'];
            }
        }

        return $this->render('operationList.html.twig', [
            'event' => $event[0],
            'balance' => $balance,
            'total' => $total
        ]);
    }

    /**
     * @Route("/operation/create/{eventId}/{userId}", name="operation_create")
     * @throws \Exception
     */
    public function operationCreate(Request $request, $eventId, $userId): Response
    {
        /** @var  EventRepository $eventRepo */
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        /** @var Event[] $event */
        $event = $eventRepo->getEventUsers($eventId);
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

        $operation = new Operation();
        $operation->setUser($user);
        $operation->setDate(new \DateTime());
        $operation->setEvent($event[0]);

        foreach ($event[0]->getUserEvents() as $userEvent) {
            $expense = new Expense();
            $expense->setUser($userEvent->getUser());
            $expense->setAmount(0);
            $expense->setOperation($operation);
            $operation->getExpenses()->add($expense);
            $payment = new Payment();
            $payment->setUser($userEvent->getUser());
            $payment->setAmount(1);
            $payment->setOperation($operation);
            $operation->getPayments()->add($payment);
        }

        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($operation);
            $entityManager->flush();

            return $this->redirectToRoute('operation_list', ['eventId' => $eventId]);
        }

        return $this->render("operationCreate.html.twig", ['form' => $form->createView(), 'event' => $event]);
    }
}
