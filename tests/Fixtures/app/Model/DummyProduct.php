<?php

namespace Locastic\Loggastic\Tests\Fixtures\App\Model;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Annotation\Groups;

#[Loggable(groups: ['dummy_product_log'])]
class DummyProduct
{
    private int $id;

    #[Groups(groups: ['dummy_product_log'])]
    private string $name;

    #[Groups(groups: ['dummy_product_log'])]
    private int $price;

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

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }
}
