<?php

namespace App\Message;

class VizAssignMessage
{
    /**
     * @param int $id
     * @param string $type 'image' or 'ProductSet'
     */
    public function __construct(private readonly int $id, private readonly string $type = 'image')
    {

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
