<?php

namespace App\Feed\Writer;

use Closure;
use Pimcore\Model\DataObject;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class FeedWriter implements FeedWriterInterface
{
    /**
     * @var Closure(int $current, int $total):void
     */
    protected Closure $status;
    /**
     * @var int total number of feed items
     */
    public readonly int $total;

    /**
     * @param array $iterable Array of objects to feed
     * @param Closure(DataObject $object):string $formatter Object toString formatter
     */
    public function __construct(private readonly array $iterable, private readonly Closure $formatter)
    {
        $this->total = count($this->iterable) ?? 1;

        $this->status = function ($current, $total) {
            if($current % 20 == 0)
            {
                $percentage = (int)($current / $total * 100);
                echo "Status: " . $percentage . "%" . ": " . $current . " / " . $total . PHP_EOL;
            }
        };
    }

    /**
     * Sets custom status function that will be triggered in each feed() iteration
     *
     * @param Closure(int $current, int $total):void $status
     * @return void
     */
    public function setStatus(Closure $status): void
    {
        $this->status = $status;
    }

    /**
     * Write feed in given stream
     *
     * @param $stream
     * @return void
     */

    public function write($stream): void
    {
        $this->begin($stream);
        $this->feed($stream);
        $this->end($stream);
    }

    /**
     * Call on each feed item
     *
     * @param $stream
     * @return void
     */
    public function feed($stream):void
    {
        $current = 1;
        foreach ($this->iterable as $item)
        {
            fwrite($stream, ($this->formatter)($item));
            $this->itemSeparator($stream, $current == $this->total);

            ($this->status)($current, $this->total);

            $current++;
        }
    }

    public function itemSeparator($stream, $last = false): void
    {

    }
}
