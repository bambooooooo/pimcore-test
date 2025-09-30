<?php

namespace App\Message;

class FeedMessage
{
    public function __construct(private int $objectId)
    {

    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }
}
