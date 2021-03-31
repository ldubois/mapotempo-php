<?php

namespace Ldubois\Mapotempo;


class Record
{
    private $id;

    private $fields;

    public function __construct(string $id, array $fields)
    {
        $this->id     = $id;
        $this->fields = $fields;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}