<?php

namespace Locastic\Loggastic\Tests\Fixtures;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Loggable(groups={"dummy_blog_post_log"})
 */
class DummyBlogPost
{
    private int $id;

    /** @Groups({"dummy_blog_post_log"}) */
    private ?string $title;

    /** @Groups({"dummy_blog_post_log"}) */
    private array $tags = [];

    /** @Groups({"dummy_blog_post_log"}) */
    private int $position = 0;

    /** @Groups({"dummy_blog_post_log"}) */
    private ?\DateTime $publishAt;

    /** @Groups({"dummy_blog_post_log"}) */
    private bool $enabled;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPublishAt(): ?\DateTime
    {
        return $this->publishAt;
    }

    public function setPublishAt(?\DateTime $publishAt): void
    {
        $this->publishAt = $publishAt;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
