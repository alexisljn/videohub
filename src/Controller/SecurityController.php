<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginUserType;
use App\Form\ProfileUserType;
use App\Form\RegisterUserType;
use App\Manager\UserManager;
use App\Manager\VideoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserManager $userManager)
    {
        $user = new User();
        $form = $this->createForm(RegisterUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->addUser($user);
            $this->addFlash('notice', 'You successfully created an account !');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route ("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $user = new User();
        $form = $this->createForm(LoginUserType::class, $user);

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(Request $request, UserManager $userManager, VideoManager $videoManager)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileUserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() &&$form->isValid()) {
            $userManager->editUser($user);
            $this->addFlash('notice', 'Edit successful !');
        }
        $publishedVideos = $videoManager->getPublishedVideosFromUser($user);
        $unpublishedVideos = $videoManager->getUnpublishedVideosFromUser($user);
        return $this->render('security/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'publishedVideos' => $publishedVideos,
            'unpublishedVideos' => $unpublishedVideos
        ]);

    }

}

