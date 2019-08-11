<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Video;
use App\Event\Video\VideoDeletedEvent;
use App\Event\Video\VideoUpdatedEvent;
use App\Form\AdminCreateCategoryType;
use App\Form\AdminCreateUserType;
use App\Form\AdminEditUserType;
use App\Form\AdminEditVideoType;
use App\Form\EditVideoType;
use App\Manager\CategoryManager;
use App\Manager\UserManager;
use App\Manager\VideoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(Request $request, UserManager $userManager, VideoManager $videoManager)
    {
        $publishedVideos = [];
        $unpublishedVideos = [];
        $user = new User();

        $form = $this->createForm(AdminCreateUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->addUser($user);
            $this->addFlash('notice', 'Successful user creation !');
        }
        $users = $userManager->getUsers();

        // Utilisation de l'ID de l'utilisateur en tant que clé du tableau pour correspondance dans la vue
        foreach ($users as $singleUser) {
            $unpublishedVideos[$singleUser->getId()] = $videoManager->getUnpublishedVideosFromUser($singleUser);
            $publishedVideos[$singleUser->getId()] = $videoManager->getPublishedVideosFromUser($singleUser);
        }
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'publishedVideos' => $publishedVideos,
            'unpublishedVideos' => $unpublishedVideos,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="admin_user")
     */
    public function editUser(User $user, Request $request, UserManager $userManager) {

        // On stocke le mot de passe avant validation du formulaire dans une variable
        $oldPassword = $user->getPassword();

        // On récupère les roles de l'utilisateur et si c'est un administrateur et qu'il
        // en a donc 2, on supprimer 'ROLE_USER' Afin de n'avoir que 'ROLE_ADMIN'
        $roles = $user->getRoles();
        if(count($roles) > 1) {
            $roles = array_slice($roles, 1);
            $user->setRoles($roles);
        }
        $form = $this->createForm(AdminEditUserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // On récupère les entrées du formulaires
            $data = $form->getData();
            // on récupère le mot de passe parmis les entrées
            $password = ($data->getpassword());
            // On compare le nouveau mot de passe avec l'ancien
            // Si c'est le même on procède à une modification sans altération du mot de passe
            if($password == $oldPassword) {
                $userManager->editUser($user, false);
            } else {
                $userManager->editUser($user);
            }
            $this->addFlash('notice','Edit successful !');
            return $this->redirectToRoute('admin_user', ['id' => $user->getId()]);
        }

        return $this->render('admin/user.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("admin/user/delete/{id}", name="delete_user")
     */
    public function deleteUser(User $user, UserManager $userManager)
    {
        $userManager->deleteUser($user);
        $this->addFlash('notice','Removal successful !');
        return $this->redirectToRoute("admin");
    }

    /**
     * @Route("admin/categories", name="admin_categories")
     */
    public function categories(Request $request, CategoryManager $categoryManager)
    {
        $category = new Category();
        $form = $this->createForm(AdminCreateCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryManager->actionOnCategory($category);
        }
        $categories = $categoryManager->getCategories();

        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("admin/category/{id}", name="admin_category")
     */
    public function editCategory(Request $request, CategoryManager $categoryManager, Category $category)
    {
        $form = $this->createForm(AdminCreateCategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryManager->actionOnCategory($category);
        }

        return $this->render('admin/category.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    /**
     * @Route("admin/category/delete/{id}", name="delete_category")
     */
    public function deleteCategory(Category $category, CategoryManager $categoryManager)
    {
        $categoryManager->deleteCategory($category);
        return $this->redirectToRoute("admin_categories");
    }

    /**
     * @Route("admin/videos", name="admin_videos")
     */
    public function getVideos(VideoManager $videoManager, CategoryManager $categoryManager)
    {
        $videos = $videoManager->getVideos();
        $categories = $categoryManager->getCategories();
        $showNullCat = false;

        foreach ($videos as $video) {
            if ($video->getCategory() === null) {
                $showNullCat = true;
            }
        }

        return $this->render('admin/videos.html.twig', [
            'videos' => $videos,
            'categories' => $categories,
            'showNullCat' => $showNullCat
        ]);
    }

    /**
     * @Route("admin/video/{id}", name="admin_video")
     */
    public function editVideo(VideoManager $videoManager, Video $video, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm(AdminEditVideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $videoManager->editVideo($video);
            $event = new VideoUpdatedEvent($video);
            $eventDispatcher->dispatch(VideoUpdatedEvent::NAME, $event);
            $this->addFlash('notice','Edit successful !');
        }
        return $this->render('admin/video.html.twig', [
            'video' => $video,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("admin/video/delete/{id}", name="admin_delete_video")
     */
    public function deleteVideo(VideoManager $videoManager, Video $video, EventDispatcherInterface $eventDispatcher)
    {
        $event = new VideoDeletedEvent($video);
        $eventDispatcher->dispatch(VideoDeletedEvent::NAME, $event);
        $videoManager->deleteVideo($video);
        $this->addFlash('notice','Removal successful !');
        return $this->redirectToRoute('admin_videos');
    }
}
