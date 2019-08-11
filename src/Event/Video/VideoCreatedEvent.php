<?php


namespace App\Event\Video;


use App\Entity\Video;
use Symfony\Component\EventDispatcher\Event;

class VideoCreatedEvent extends Event
{
    const NAME = "video.created";
    protected $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function getVideo()
    {
        return $this->video;
    }

}