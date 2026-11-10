<?php

namespace App\Tests\Unit\Service;

use App\Service\Xlsx\XlsxImageEmbedder;
use Codeception\Test\Unit;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Image thumbnails in XLSX grid exports of Pimcore Studio (App\Studio\Export\ImageEmbeddingXlsxExportService).
 */
class XlsxImageEmbedderTest extends Unit
{
    private array $requested = [];

    /**
     * @dataProvider cellValues
     */
    public function testRecognisesImageReferences(mixed $value, ?array $expected): void
    {
        $this->assertSame($expected, (new XlsxImageEmbedder($this->thumbnails()))->extractImagePaths($value));
    }

    public static function cellValues(): array
    {
        return [
            'asset path of an image' => ['/Products/chair.jpg', ['/Products/chair.jpg']],
            'upper case extension' => ['/Products/CHAIR.PNG', ['/Products/CHAIR.PNG']],
            'markdown images' => ['![a](/x/1.jpg) ![b](/x/2.png)', ['/x/1.jpg', '/x/2.png']],
            'empty gallery' => ['[]', []],
            'plain text' => ['Chair oak', null],
            'pdf path' => ['/Docs/manual.pdf', null],
            'number' => [12, null],
            'empty' => ['', null],
            'base64 of something else' => [base64_encode('hello'), null],
        ];
    }

    public function testEmbedsThumbnailsAndClearsCells(): void
    {
        $spreadsheet = $this->sheet([
            ['Key', 'Image', 'Name'],
            ['A-1', '/Products/a.jpg', 'Chair'],
            ['A-2', '![x](/Products/b.jpg) ![y](/Products/c.jpg)', 'Table'],
        ]);

        $embedded = (new XlsxImageEmbedder($this->thumbnails()))->embed($spreadsheet);

        $ws = $spreadsheet->getActiveSheet();
        $this->assertSame(3, $embedded);
        $this->assertSame(['/Products/a.jpg', '/Products/b.jpg', '/Products/c.jpg'], $this->requested);
        $this->assertNull($ws->getCell('B2')->getValue());
        $this->assertSame('Chair', $ws->getCell('C2')->getValue(), 'other cells are kept');
        $this->assertSame(['B2', 'B3', 'B3'], array_map(static fn ($d) => $d->getCoordinates(), iterator_to_array($ws->getDrawingCollection())));
        $this->assertSame(XlsxImageEmbedder::IMAGE_SIZE, $ws->getDrawingCollection()[2]->getOffsetX(), 'second image of a cell is moved right');
        $this->assertEquals(XlsxImageEmbedder::ROW_HEIGHT, $ws->getRowDimension(3)->getRowHeight());
        $this->assertEquals(2 * XlsxImageEmbedder::CELL_WIDTH, $ws->getColumnDimension('B')->getWidth());
    }

    public function testMissingAssetsLeaveAnEmptyCell(): void
    {
        $spreadsheet = $this->sheet([['/Products/deleted.jpg']]);

        $embedded = (new XlsxImageEmbedder(static fn () => null))->embed($spreadsheet);

        $this->assertSame(0, $embedded);
        $this->assertNull($spreadsheet->getActiveSheet()->getCell('A1')->getValue());
    }

    public function testSpreadsheetWithoutImagesIsUntouched(): void
    {
        $spreadsheet = $this->sheet([['Key', 'Name'], ['A-1', 'Chair']]);

        $this->assertSame(0, (new XlsxImageEmbedder($this->thumbnails()))->embed($spreadsheet));
        $this->assertSame('Chair', $spreadsheet->getActiveSheet()->getCell('B2')->getValue());
        $this->assertCount(0, $spreadsheet->getActiveSheet()->getDrawingCollection());
    }

    private function thumbnails(): \Closure
    {
        return function (string $path): string {
            $this->requested[] = $path;
            $file = tempnam(sys_get_temp_dir(), 'thumb_');
            imagepng(imagecreatetruecolor(4, 4), $file);

            return $file;
        };
    }

    private function sheet(array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);

        return $spreadsheet;
    }
}
