<?php

namespace App\Feed\Writer;

abstract class CsvFeedWriter extends FeedWriter
{
    public function contentType(): string
    {
        return 'text/csv';
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function end($stream): void
    {

    }
}
