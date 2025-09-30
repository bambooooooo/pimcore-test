<?php

namespace App\Traits;

use Pimcore\Db;

trait StockTrait
{
    public function getStock(): int
    {
        return Db::get()->fetchOne('SELECT stock FROM stocks WHERE o_id = ?', [$this->getId()]) ?? 0;
    }

    public function setStock(int $stock): self
    {
        Db::get()->executeStatement('INSERT INTO stocks (o_id, stock) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE stock = VALUES(stock)', [$this->getId(), $stock]);

        return $this;
    }
}
