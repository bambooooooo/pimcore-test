<?php

namespace App\Message;

class PsMessage
{
    public function __construct(private int $id, private string $mode = "update", private int $userId = 0)
    {

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
