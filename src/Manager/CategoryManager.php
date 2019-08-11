<?php


namespace App\Manager;


use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryManager
{
    private $categoryRepository;
    private $entityManager;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    public function getCategories()
    {
        $categories = $this->categoryRepository->findAll();

        return $categories;
    }

    public function getCategory(string $title)
    {
        $result = $this->categoryRepository->findOneBy(['title' => $title]);

        return $result;
    }

    public function actionOnCategory(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function deleteCategory(Category $category)
    {
        $videos = $category->getVideos();
        foreach ($videos as $video) {
            $video->setCategory();
        }
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}