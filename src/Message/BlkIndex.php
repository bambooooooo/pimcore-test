<?php

namespace App\Message;

class BlkIndex
{
    public function __construct(private int $objectId)
    {

    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }
}
