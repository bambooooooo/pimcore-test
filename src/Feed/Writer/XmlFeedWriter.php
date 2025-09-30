<?php

namespace App\Feed\Writer;
use Closure;

class XmlFeedWriter extends FeedWriter
{
    public function __construct(private readonly array $iterable, private readonly Closure $formatter)
    {
        parent::__construct($this->iterable, $this->formatter);
    }

    public function begin($stream): void
    {
        $now = date('d.m.Y H:i:s');
        fwrite($stream, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><products version=\"$now\">");
    }

    public function end($stream): void
    {
        fwrite($stream, "</products>");
    }

    public function contentType(): string
    {
        return 'application/xml';
    }

    public function extension(): string
    {
        return 'xml';
    }
}
