<?php

namespace App\Feed\Writer;

abstract class JsonFeedWriter extends FeedWriter
{
    public function begin($stream):void
    {
        fwrite($stream, '{"feed":[');
    }

    public function end($stream):void
    {
        fwrite($stream, ']}');
    }

    public function contentType(): string
    {
        return 'application/json';
    }

    public function extension(): string
    {
        return 'json';
    }

    public function itemSeparator($stream, $last = false): void
    {
        if(!$last) {
            fwrite($stream, ',');
        }
    }
}
