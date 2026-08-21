<?php

namespace App\Model\Interface;

interface StockInterface
{
    public function getStock(): string;
    public function setStock(int $stock): self;
}
