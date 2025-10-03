<?php

namespace App\Message;

class PsMessage
{
    public function __construct(private int $objectId)
    {

    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }
}
