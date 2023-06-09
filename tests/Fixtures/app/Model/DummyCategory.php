<?php

namespace Locastic\Loggastic\Tests\Fixtures\App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class DummyCategory
{
    #[Groups(groups: ['dummy_category_log'])]
    private ?int $id = null;

    #[Groups(groups: ['dummy_category_log'])]
    private ?string $name = null;

    #[Groups(groups: ['dummy_photo_log'])]
    private ?DummyPhoto $photo = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPhoto(): ?DummyPhoto
    {
        return $this->photo;
    }

    public function setPhoto(?DummyPhoto $photo): void
    {
        $this->photo = $photo;
    }
}
