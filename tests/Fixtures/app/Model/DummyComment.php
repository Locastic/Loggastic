<?php

namespace Locastic\Loggastic\Tests\Fixtures\App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

class DummyComment
{
    #[Groups(groups: ['dummy_photo_log', 'dummy_blog_post_log'])]
    private Uuid $id;

    #[Groups(groups: ['dummy_photo_log', 'dummy_blog_post_log'])]
    private ?string $content = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
