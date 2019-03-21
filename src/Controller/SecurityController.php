<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
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
     * @route("/signin", name="app_signup")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function signin(AuthenticationUtils $authenticationUtils): Response
    {
        return new Response("signup");
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
        return new Response("logout");
    }
}
