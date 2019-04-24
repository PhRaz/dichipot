<?php

namespace App\Controller;

use App\Bridge\AwsCognitoClient;
use App\Entity\Event;
use App\Entity\Expense;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Form\OperationType;
use App\Form\UserType;
use App\Form\EventType;
use App\Repository\OperationRepository;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Service\Helper;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

    /** @var Helper */
    var $helper;

    public function __construct(AwsCognitoClient $cognitoClient, Helper $helper)
    {
        $this->cognitoClient = $cognitoClient;
        $this->helper = $helper;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $event = new Event();

        /*
         * admin is a predefined user on the created event
         */
        $userEvent = new UserEvent();
        $userEvent->setAdministrator(true);
        $admin->addUserEvent($userEvent);
        $event->setDate(new \DateTime());
        $event->addUserEvent($userEvent);

        $form = $this->createForm(eventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($event->getUserEvents() as $userEvent) {
                $userEvent
                    ->setDate(new \DateTime())
                    ->setEvent($event);
                if ($userEvent->getAdministrator() === null) {
                    $userEvent->setAdministrator(false);
                }

                /** @var User $user */
                $user = $userEvent->getUser();
                /** @var UserRepository $userRepo */
                $userRepo = $this->getDoctrine()->getRepository(User::class);
                /** @var User $userCheck */
                $userCheck = $userRepo->findOneBy(['mail' => $user->getMail()]);
                if (is_null($userCheck)) {
                    /*
                     * it is a new user
                     */
                    $user->setDate(new \DateTime());
                    $user->setName(explode('@', $user->getMail())[0]);
                    $entityManager->persist($user);
                } else {
                    /*
                     * the user exists already
                     */
                    $userCheck->addUserEvent($userEvent);
                }
                $entityManager->persist($userEvent);

                /*
                 * TODO manage async operation on user creation
                 */
                try {
                    $this->cognitoClient->adminCreateUser($user->getMail());
                } catch (CognitoIdentityProviderException $e) {
                    if ($e->getAwsErrorCode() == 'UsernameExistsException') {
                        /*
                         * TODO send a mail for information to the user (if admin / if user)
                         */
                    } else {
                        $this->addFlash('danger', $e->getAwsErrorMessage() . " (" . $user->getMail() . ")");
                    }
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }

        return $this->render('eventCreate.html.twig', ['form' => $form->createView()]);
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

        $balance = array();
        $total = array();
        if (count($event->getOperations()) > 0) {
            $event = $eventRepo->getEventOperations($eventId, true);
            list($balance, $total) = $this->helper->getBalance($event);
        }

        return $this->render('operationList.html.twig', [
            'user' => $user,
            'event' => $event,
            'balance' => $balance,
            'total' => $total
        ]);
    }

    /**
     * @Route("/operation/create/{eventId}", name="operation_create")
     * @param Request $request
     * @param $eventId
     * @return Response
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
        $admin = $userRepo->findOneBy(['mail' => $authenticatedUser->getEmail()]);

        /** @var array $users */
        $users = $userRepo->getEventUsers($eventId);

        /** @var Operation $operation */
        $operation = new Operation();

        /** @var Event $event */
        $event = $users[0]->getUserEvents()[0]->getEvent();

        /*
         * init operation and relate to the event
         */
        $operation->setUser($admin);
        $operation->setDate(new \DateTime());
        $event->addOperation($operation);

        foreach ($users as $user) {
            /*
             * for each user attach an expense to the operation,
             * users are ordered on pseudo field
             */
            $expense = new Expense();
            $expense->setUser($user);
            $expense->setExpense(0);
            $expense->setPayment(1);
            $operation->addExpense($expense);
        }

        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($operation);
            $entityManager->flush();

            return $this->redirectToRoute('operation_list', ['eventId' => $eventId]);
        }

        return $this->render("operationCreate.html.twig", ['form' => $form->createView(), 'eventId' => $event->getId()]);
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

    /**
     * @route("/operation/remove/{operationId}", name="operation_remove")
     * @param Request $request
     * @param integer $operationId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function operationRemove(Request $request, $operationId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var OperationRepository $operationRepo */
        $operationRepo = $this->getDoctrine()->getRepository(Operation::class);

        /** @var Operation $operation */
        $operation = $operationRepo->findForUpdate($operationId);

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $eventId = $operation->getEvent()->getId();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($operation);
            $entityManager->flush();

            return $this->redirectToRoute('operation_list', ['eventId' => $eventId]);
        }

        return $this->render("operationRemove.html.twig", ['form' => $form->createView(), 'operation' => $operation]);
    }
}
