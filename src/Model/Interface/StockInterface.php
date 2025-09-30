<?php

namespace App\Model\Interface;

interface StockInterface
{
    public function getStock(): int;
    public function setStock(int $stock): self;
}
