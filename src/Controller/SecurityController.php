<?php

namespace App\Controller;

use App\Bridge\AwsCognitoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'info' => 'coucou vous allez recevoir un mail']);
    }

    /**
     * @route("/signup", name="app_signup")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function signup(Request $request, AuthenticationUtils $authenticationUtils): Response
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
            $this->cognitoClient->signUp($data['email'], $data['password']);

            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'info' => 'Vous allez recevoir un mail qui vous permetra de confirmer votre email.'
            ]);
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
    public function resetPassword(AuthenticationUtils $authenticationUtils): Response
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
