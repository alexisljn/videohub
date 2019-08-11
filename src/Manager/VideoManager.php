<?php


namespace App\Manager;


use App\Entity\Category;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;

class VideoManager
{
    private $videoRepository;
    private $entityManager;

    public function __construct(VideoRepository $videoRepository, EntityManagerInterface $entityManager)
    {
        $this->videoRepository = $videoRepository;
        $this->entityManager = $entityManager;
    }

    public function getPublishedVideos()
    {
        $publishedVideos = $this->videoRepository->findBy(['published' => true]);
        return $publishedVideos;
    }

    public function getPublishedVideosFromUser(User $user)
    {
        $userPublishedVideos = $this->videoRepository->findBy(['published' => true, 'user' => $user]);
        return $userPublishedVideos;
    }

    public function getUnpublishedVideosFromUser(User $user)
    {
        $userUnpublishedVideos = $this->videoRepository->findBy(['published' => false, 'user' => $user]);
        return $userUnpublishedVideos;
    }

    public function getVideos()
    {
        $videos = $this->videoRepository->findAll();
        return $videos;
    }

    public function getPublishedNoCategoryVideos()
    {
        $videos = $this->videoRepository->findBy(['published' => true, 'category' => null]);
        return $videos;
    }

    public function countVideosByCategory(Category $category)
    {
        $videos = $this->videoRepository->count(['category' => $category]);
        return $videos;
    }

    public function addVideo(Video $video, User $user)
    {
        $video->setUser($user);
        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    public function editVideo(Video $video)
    {
        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    public function deleteVideo(Video $video)
    {
        $this->entityManager->remove($video);
        $this->entityManager->flush();
    }
}