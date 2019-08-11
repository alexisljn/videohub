<?php

namespace App\Controller;

use App\Manager\CategoryManager;
use App\Manager\VideoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(VideoManager $videoManager, CategoryManager $categoryManager)
    {
        $videoIds = [];
        $publishedVideos = $videoManager->getPublishedVideos();
        $categories = $categoryManager->getCategories();
        $noCategoryVideos = $videoManager->getPublishedNoCategoryVideos();

        // Utilisation des ID des vidéos en tant que clé afin de faire matcher les indices du tableau aux vidéos dans la vue
        foreach ($publishedVideos as $video) {
            $videoIds[$video->getId()] = substr($video->getUrl(), strpos($video->getUrl(), '?v=') + 3);
        }
        foreach ($noCategoryVideos as $video) {
            $videoIds[$video->getId()] = substr($video->getUrl(), strpos($video->getUrl(), '?v=') + 3);
        }

        return $this->render('home/index.html.twig', [
            'videos' => $publishedVideos,
            'categories' => $categories,
            'noCategoryVideos' => $noCategoryVideos,
            'videoIds' => $videoIds
        ]);
    }
}
