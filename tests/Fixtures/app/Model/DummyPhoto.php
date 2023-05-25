<?php

namespace Locastic\Loggastic\Tests\Fixtures\App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class DummyPhoto
{
    #[Groups(groups: ['dummy_photo_log'])]
    private $id;

    #[Groups(groups: ['dummy_photo_log'])]
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
