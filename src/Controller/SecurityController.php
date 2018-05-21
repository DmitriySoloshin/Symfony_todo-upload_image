<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/registration", name="registration")
     */
    public function registration(Request $r, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form= $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($r);
        if($form->isSubmitted() && $form->isValid()){
            $pass = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($pass);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect('login');
        }

        return $this->render('security/registration.html.twig',['form'=> $form->createView()]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $r, AuthenticationUtils $auth)
    {
        //get auth errors
        $error = $auth->getLastAuthenticationError();
        $lastUsername = $auth->getLastUsername();
        return $this->render('security/login.html.twig',
            ['error'=> $error,
             'last_username'=> $lastUsername
            ]);
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}
}
