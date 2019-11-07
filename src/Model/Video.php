<?php


namespace Jackal\Youtubbalo\Model;

class Video
{
    private $title;

    private $description;

    private $file;

    public function __construct($file, $title, $description)
    {
        $this->file = new \SplFileObject($file);
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \SplFileObject
     */
    public function getFile()
    {
        return $this->file;
    }
}
