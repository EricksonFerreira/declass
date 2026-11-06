<?php

namespace App\DTO;

class BreadCrumbItem
{
    private $name;

    private $url;

    public function __construct(string $name, ?string $url = null)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
