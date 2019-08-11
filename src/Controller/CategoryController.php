<?php

namespace App\Controller;

use App\Entity\Category;
use App\Manager\VideoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category/{id}", name="category_id")
     */
    public function index(Category $category)
    {
        return $this->render('category/index.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/nocategory", name="no_category")
     */
    public function noCategory(VideoManager $videoManager)
    {
        $videos = $videoManager->getPublishedNoCategoryVideos();
        return $this->render('category/nocat.html.twig', [
            'videos' => $videos
        ]);
    }
}
