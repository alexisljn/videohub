<?php


namespace App\Subscriber;


use App\Event\Video\VideoCreatedEvent;
use App\Event\Video\VideoDeletedEvent;
use App\Event\Video\VideoUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VideoSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface  $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
            return [
                VideoCreatedEvent::NAME => 'onVideoCreated',
                VideoUpdatedEvent::NAME => 'onVideoUpdated',
                VideoDeletedEvent::NAME => 'onVideoDeleted'
            ];
    }

    public function onVideoCreated(VideoCreatedEvent $videoCreatedEvent)
    {
        $video = $videoCreatedEvent->getVideo();
        $this->logger->notice($video->getUser()->getEmail().' uploaded video N°'.$video->getId()." - ".$video->getTitle());
    }

    public function onVideoUpdated(VideoUpdatedEvent $videoUpdatedEvent)
    {
        $video = $videoUpdatedEvent->getVideo();
        $this->logger->notice($video->getUser()->getEmail().' updated video N°'.$video->getId()." - ".$video->getTitle());
    }

    public function onVideoDeleted(VideoDeletedEvent $videoDeletedEvent)
    {
        $video = $videoDeletedEvent->getVideo();
        $this->logger->notice('video N°'.$video->getId()." - ".$video->getTitle().' get deleted');
    }


}