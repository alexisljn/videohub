<?php

namespace App\Controller;

use App\Entity\Video;
use App\Event\Video\VideoCreatedEvent;
use App\Event\Video\VideoDeletedEvent;
use App\Event\Video\VideoUpdatedEvent;
use App\Form\EditVideoType;
use App\Form\UploadVideoType;
use App\Manager\VideoManager;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    /**
     * @Route("/video/{id}", name="video_id")
     */
    public function index(Video $video)
    {
        $user = $this->getUser();
        $url = $video->getUrl();
        // Extraction de l'uuid de la vidéo Youtube
        $videoId= substr($url, strpos($url, '?v=') + 3);

        return $this->render('video/index.html.twig', [
            'video' => $video,
            'videoId' => $videoId,
            'user' => $user
        ]);
    }

    /**
     * @Route("/upload", name="upload")
     */
    public function upload(Request $request, VideoManager $videoManager,EventDispatcherInterface $eventDispatcher)
    {
        $video = new Video();
        $form = $this->createForm(UploadVideoType::class, $video);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $videoManager->addVideo($video, $user);
            $event = new VideoCreatedEvent($video);
            $eventDispatcher->dispatch(VideoCreatedEvent::NAME, $event);
            $this->addFlash('notice','Upload successful !');
            return $this->redirectToRoute('video_id',['id' => $video->getId()]);
        }

        return $this->render('video/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_video")
     */
    public function edit(Request $request, VideoManager $videoManager, Video $video, EventDispatcherInterface $eventDispatcher)
    {
        // Ne peut acceder à cette page qu'un administrateur ou l'utilisateur concerné par cette vidéo
        $this->denyAccessUnlessGranted(new Expression('"ROLE_ADMIN" in roles or user.getId() == object.getUser().getId()'), $video);
        $form = $this->createForm(EditVideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $videoManager->editVideo($video);
            $event = new VideoUpdatedEvent($video);
            $eventDispatcher->dispatch(VideoUpdatedEvent::NAME, $event);
            $this->addFlash('notice', 'Edit successful !');
            return $this->redirectToRoute('video_id', ['id' => $video->getId()]);
        }

        return $this->render('video/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_video")
     */
    public function delete(Video $video, VideoManager $videoManager, EventDispatcherInterface $eventDispatcher)
    {
        // Ne peut acceder à cette page qu'un administrateur ou l'utilisateur concerné par cette vidéo
        $this->denyAccessUnlessGranted(new Expression('"ROLE_ADMIN" in roles or user.getId() == object.getUser().getId()'), $video);
        $event = new VideoDeletedEvent($video);
        $eventDispatcher->dispatch(VideoDeletedEvent::NAME, $event);
        $videoManager->deleteVideo($video);
        $this->addFlash('notice', 'Removal successful !');

        return $this->redirectToRoute('profile');
    }
}
