<?php


namespace App\Event\Video;


use App\Entity\Video;
use Symfony\Component\EventDispatcher\Event;

class VideoDeletedEvent extends Event
{
    const NAME = 'video.deleted';
    protected $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @return Video
     */
    public function getVideo(): Video
    {
        return $this->video;
    }
}