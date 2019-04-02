<?php

namespace App\Controller;

use App\Bridge\AwsCognitoClient;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Regex;


class SecurityController extends AbstractController
{
    /** @var AwsCognitoClient */
    var $cognitoClient;

    public function __construct(AwsCognitoClient $cognitoClient)
    {
        $this->cognitoClient = $cognitoClient;
    }

    /**
     * @route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('danger', 'Login ou mot de passe incorrect.');
        }
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername]);
    }

    /**
     * @route("/signup", name="app_signup")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @throws \Exception
     * @return Response
     */
    public function signup(Request $request): Response
    {
        $defaultData = [
            'email' => '',
            'password' => ''
        ];
        $form = $this->createFormBuilder($defaultData)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/',
                        'htmlPattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$'
                    ])
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->cognitoClient->signUp($data['email'], $data['password']);
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->render('security/signup.html.twig', ['form' => $form->createView()]);
            }

            $user = new User();
            $user->setDate(new \DateTime());
            $user->setMail($data['email']);
            $user->setName(explode('@', $data['email'])[0]);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Vous allez recevoir un mail qui contient un lien vous permettant de confirmer votre inscription. Vous pourrez ensuite vous connecter avec vos identifiants.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/signup.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @route("/confirmsignup/{userName}/{code}", name="app_confirm_signup")
     * @param string $userName
     * @param string $code
     * @return Response
     */
    public function confirmSignup($userName, $code): Response
    {
        $result = $this->cognitoClient->confirmSignUp($userName, $code);
    }

    /**
     * @route("/resetpassword", name="app_reset_password")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function resetPassword(): Response
    {
        return new Response("resetPassword");
    }

    /**
     * @route("/logout", name="app_logout")
     * @return Response
     */
    public function logout(): Response
    {
        return $this->redirectToRoute('home');
    }
}
