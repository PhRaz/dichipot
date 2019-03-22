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

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @route("/signup", name="app_signup")
     * @param Request $request
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
            ->add('password', PasswordType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->cognitoClient->signUp($data['email'], $data['password']);
            return $this->redirectToRoute('home');
        }
        return $this->render('security/signup.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @route("/confirmsignup", name="app_confirm_signup")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function confirmSignup(AuthenticationUtils $authenticationUtils): Response
    {
        $result = $this->cognitoClient->confirmSignUp("testcognito@yopmail.com", "862948");
        print_r($result);
        die();
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
