<?php

namespace App\Tests\Unit\Feed;

use App\Feed\Writer\CsvFeedWriter;
use App\Feed\Writer\JsonFeedWriter;
use App\Feed\Writer\XmlFeedWriter;
use Codeception\Test\Unit;

class FeedWriterTest extends Unit
{
    /**
     * @dataProvider itemCounts
     */
    public function testJsonFeedIsValidJson(array $items): void
    {
        $writer = new class($items, fn (array $item) => json_encode($item)) extends JsonFeedWriter {};

        $json = $this->write($writer);

        $this->assertSame(['feed' => $items], json_decode($json, true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame('application/json', $writer->contentType());
        $this->assertSame('json', $writer->extension());
    }

    /**
     * @dataProvider itemCounts
     */
    public function testXmlFeedIsWellFormed(array $items): void
    {
        $writer = new XmlFeedWriter($items, fn (array $item) => "<product id=\"{$item['id']}\"/>");

        $xml = simplexml_load_string($this->write($writer));

        $this->assertNotFalse($xml);
        $this->assertSame('products', $xml->getName());
        $this->assertCount(count($items), $xml->product);
        $this->assertMatchesRegularExpression('/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', (string) $xml['version']);
        $this->assertSame('application/xml', $writer->contentType());
        $this->assertSame('xml', $writer->extension());
    }

    public function testCsvFeedWritesHeaderThenRows(): void
    {
        $writer = new class([['id' => 1], ['id' => 2]], fn (array $item) => "{$item['id']}\n") extends CsvFeedWriter {
            public function begin($stream): void
            {
                fwrite($stream, "id\n");
            }
        };

        $this->assertSame("id\n1\n2\n", $this->write($writer));
        $this->assertSame('text/csv', $writer->contentType());
    }

    public function testStatusCallbackReceivesProgress(): void
    {
        $writer = new XmlFeedWriter([1, 2, 3], fn () => '<p/>');
        $calls = [];
        $writer->setStatus(function (int $current, int $total) use (&$calls) {
            $calls[] = "$current/$total";
        });

        $this->write($writer, keepStatus: true);

        $this->assertSame(['1/3', '2/3', '3/3'], $calls);
        $this->assertSame(3, $writer->total);
    }

    public static function itemCounts(): array
    {
        return [
            'empty' => [[]],
            'single' => [[['id' => 1]]],
            'many' => [[['id' => 1], ['id' => 2], ['id' => 3]]],
        ];
    }

    private function write(object $writer, bool $keepStatus = false): string
    {
        if (!$keepStatus)
        {
            $writer->setStatus(static fn () => null); // default status echoes progress
        }

        $stream = fopen('php://memory', 'w+');
        $writer->write($stream);
        rewind($stream);

        return stream_get_contents($stream);
    }
}
