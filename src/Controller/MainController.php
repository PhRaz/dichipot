<?php

namespace App\Controller;

use App\Bridge\AwsCognitoClient;
use App\Entity\Event;
use App\Entity\Expense;
use App\Entity\Payment;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Form\OperationType;
use App\Form\UserType;
use App\Form\EventType;
use App\Repository\OperationRepository;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class MainController
 * @package App\Controller
 */
class MainController extends AbstractController
{
    /** @var AwsCognitoClient */
    var $cognitoClient;

    public function __construct(AwsCognitoClient $cognitoClient)
    {
        $this->cognitoClient = $cognitoClient;
    }

    /**
     * @route("/", name="home")
     * @return Response
     */
    public function home(): Response
    {
        return $this->render("home.html.twig");
    }

    /**
     * @route("/event/list", name="event_list")
     * @return Response
     * @throws \Exception
     */
    public function eventList(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Security\User $loggedUser */
        $loggedUser = $this->getUser();
        $mail = $loggedUser->getEmail();

        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneBy(['mail' => $mail]);
        if (is_null($user)) {
            /*
             * should never occurs
             */
            $this->addFlash('danger', 'Your account does not exist.');
            return ($this->redirectToRoute("home"));
        }
        $userId = $user->getId();
        $data = $userRepo->getUserEvents($userId);

        return $this->render("eventList.html.twig", ['user' => $data]);
    }


    /**
     * @route("/event/create/{id}", name="event_create")
     * @param $request Request
     * @param $admin User
     * @return Response
     * @throws \Exception
     */
    public function eventCreate(Request $request, User $admin)
    {
        $event = new Event();

        $form = $this->createForm(eventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $event->setDate(new \DateTime());
            $entityManager->persist($event);

            /*
             * admin is a user on the created event
             */
            $userEvent = new UserEvent();
            $userEvent
                ->setDate(new \DateTime())
                ->setAdministrator(true)
                ->setPseudo(explode('@', $admin->getMail())[0])
                ->setUser($admin)
                ->setEvent($event);
            $entityManager->persist($userEvent);

            foreach ($event->getUserEvents() as $userEvent) {
                $userEvent
                    ->setDate(new \DateTime())
                    ->setAdministrator(false)
                    ->setEvent($event);
                $user = $userEvent->getUser();
                $user->setDate(new \DateTime());
                $user->setName(explode('@', $user->getMail())[0]);
                $entityManager->persist($userEvent);
                $entityManager->persist($user);
                /*
                 * TODO manage async operation on user creation
                 */
                try {
                    $this->cognitoClient->adminCreateUser($user->getMail());
                } catch (CognitoIdentityProviderException $e) {
                    if ($e->getCode() != 'UsernameExistsException') {
                        /*
                         * already known user, not an error
                         */
                        throw($e);
                    }
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }

        return $this->render('eventCreate.html.twig', ['form' => $form->createView(), 'admin' => $admin]);
    }

    /**
     * @route("/operation/list/{eventId}", name="operation_list")
     * @param integer $eventId
     * @return Response
     * @throws \Exception
     */
    public function operationList($eventId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Security\User $authenticatedUser */
        $authenticatedUser = $this->getUser();

        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        /** @var User $user */
        $user = $userRepo->findOneBy(['mail' => $authenticatedUser->getEmail()]);

        /** @var EventRepository $eventRepo */
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);

        /** @var Event $event */
        $event = $eventRepo->getEventOperations($eventId);

        /*
         * compute balance
         */
        $balance = array();
        $grandTotal = 0;
        foreach ($event->getOperations() as $operation) {

            $id = $operation->getId();

            $totalExpense = 0;
            foreach ($operation->getExpenses() as $expense) {
                $pseudo = $expense->getUser()->getUserEvents()[0]->getPseudo();
                $grandTotal += $expense->getAmount();
                $totalExpense += $expense->getAmount();
                $balance[$id][$expense->getUser()->getName()]['expense'] = $expense->getAmount();
                $balance[$id][$expense->getUser()->getName()]['pseudo'] = $pseudo;
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
                if (!isset($total[$userName])) {
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
            'user' => $user,
            'event' => $event,
            'balance' => $balance,
            'total' => $total
        ]);
    }

    /**
     * @Route("/operation/create/{eventId}/{userId}", name="operation_create")
     * @throws \Exception
     */
    public function operationCreate(Request $request, $eventId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Security\User $authenticatedUser */
        $authenticatedUser = $this->getUser();

        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        /** @var User $user */
        $user = $userRepo->findOneBy(['mail' => $authenticatedUser->getEmail()]);

        /** @var  EventRepository $eventRepo */
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);

        /** @var Event $event */
        $event = $eventRepo->getEventUsers($eventId);

        $operation = new Operation();
        $operation->setUser($user);
        $operation->setDate(new \DateTime());
        $operation->setEvent($event);

        foreach ($event->getUserEvents() as $userEvent) {
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

    /**
     * @route("/operation/update/{operationId}", name="operation_update")
     * @param Request $request
     * @param integer $operationId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function operationUpdate(Request $request, $operationId)
    {
        /** @var OperationRepository $operationRepo */
        $operationRepo = $this->getDoctrine()->getRepository(Operation::class);

        /** @var Operation $operation */
        $operation = $operationRepo->findForUpdate($operationId);

        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $eventId = $operation->getEvent()->getId();

            return $this->redirectToRoute('operation_list', ['eventId' => $eventId]);
        }

        return $this->render("operationUpdate.html.twig", ['form' => $form->createView(), 'operation' => $operation]);
    }
}
