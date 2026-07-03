<?php

namespace Locastic\Loggastic\Tests\Fixtures\App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class DummyPhoto
{
    #[Groups(groups: ['dummy_photo_log', 'dummy_blog_post_log'])]
    private $id;

    #[Groups(groups: ['dummy_photo_log', 'dummy_blog_post_log'])]
    private $path;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path): void
    {
        $this->path = $path;
    }
}
