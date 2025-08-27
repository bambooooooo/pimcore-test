<?php

namespace App\Feed\Writer;

interface FeedWriterInterface
{
    /**
     * Content mime type
     *
     * @return string
     */
    public function contentType(): string;

    /**
     * File extension (without dot prefix). For example: xml, json, csv
     *
     * @return string
     */
    public function extension(): string;
    /**
     * Start of feed.
     *
     * For example:
     *
     * for xml - <?xml version=...
     *
     * for json - {"feed":[,
     *
     * for csv - "col1","col2",...
     *
     * @param $stream resource Stream that be written
     * @return void
     */
    function begin($stream): void;

    /**
     * Determines how single object is parsed as a string
     *
     * @param $stream
     * @return void
     */
    function feed($stream): void;

    /**
     * Call afer each object feed to fill format requirements.
     *
     * For example: json objects in array must be separated by comma
     *
     * @return string
     */
    function itemSeparator($stream, $last = false): void;

    /**
     * End of feed.
     *
     * @param $stream
     * @return void
     */
    function end($stream): void;
}
