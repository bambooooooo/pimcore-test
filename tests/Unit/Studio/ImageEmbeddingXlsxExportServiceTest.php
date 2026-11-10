<?php

namespace App\Tests\Unit\Studio;

use App\Service\Xlsx\XlsxImageEmbedder;
use App\Studio\Export\ImageEmbeddingXlsxExportService;
use Codeception\Test\Unit;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Pimcore\Bundle\StudioBackendBundle\Element\Service\StorageServiceInterface;
use Pimcore\Bundle\StudioBackendBundle\Export\Model\GridExportData;
use Pimcore\Bundle\StudioBackendBundle\Export\Service\ExportServiceInterface;
use Pimcore\Model\UserInterface;

/**
 * Pimcore Studio XLSX grid export with embedded image thumbnails.
 */
class ImageEmbeddingXlsxExportServiceTest extends Unit
{
    private string $dir;
    private Filesystem $storage;

    protected function _before(): void
    {
        $this->dir = sys_get_temp_dir() . '/xlsx-export-' . uniqid();
        $this->storage = new Filesystem(new LocalFilesystemAdapter($this->dir));
    }

    protected function _after(): void
    {
        (new \Symfony\Component\Filesystem\Filesystem())->remove($this->dir);
    }

    public function testEmbedsImagesIntoTheFileWrittenByStudio(): void
    {
        $service = $this->service([['Key', 'Image'], ['A-1', '/Products/a.jpg']]);

        $service->createExportFile(7, new GridExportData([], [], [], false), $this->createMock(UserInterface::class));

        $sheet = $this->readBack(7)->getActiveSheet();
        $this->assertSame('download-xlsx-7/download-xlsx-7.xlsx', ImageEmbeddingXlsxExportService::filePath(7));
        $this->assertCount(1, $sheet->getDrawingCollection());
        $this->assertSame('B2', $sheet->getDrawingCollection()[0]->getCoordinates());
        $this->assertNull($sheet->getCell('B2')->getValue());
    }

    public function testExportWithoutImagesStaysAsWritten(): void
    {
        $service = $this->service([['Key', 'Name'], ['A-1', 'Chair']]);
        $service->createExportFile(8, new GridExportData([], [], [], false), $this->createMock(UserInterface::class));

        $sheet = $this->readBack(8)->getActiveSheet();
        $this->assertCount(0, $sheet->getDrawingCollection());
        $this->assertSame('Chair', $sheet->getCell('B2')->getValue());
    }

    public function testCleanUpIsDelegated(): void
    {
        $inner = $this->createMock(ExportServiceInterface::class);
        $inner->expects($this->once())->method('cleanUpFileSystem')->with(9, 'folder', 'file');

        (new ImageEmbeddingXlsxExportService($inner, $this->storageService(), new XlsxImageEmbedder()))->cleanUpFileSystem(9, 'folder', 'file');
    }

    /** Inner service = Studio's XlsxExportService, simulated by writing the given rows. */
    private function service(array $rows): ImageEmbeddingXlsxExportService
    {
        $inner = $this->createMock(ExportServiceInterface::class);
        $inner->method('createExportFile')->willReturnCallback(function (int $id) use ($rows): void {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($rows);
            $file = tempnam(sys_get_temp_dir(), 'xlsx');
            (new XlsxWriter($spreadsheet))->save($file);
            $this->storage->write(ImageEmbeddingXlsxExportService::filePath($id), file_get_contents($file));
            unlink($file);
        });

        $thumbnails = static function (): string {
            $file = tempnam(sys_get_temp_dir(), 'thumb_');
            imagepng(imagecreatetruecolor(4, 4), $file);

            return $file;
        };

        return new ImageEmbeddingXlsxExportService($inner, $this->storageService(), new XlsxImageEmbedder($thumbnails));
    }

    private function storageService(): StorageServiceInterface
    {
        $storageService = $this->createMock(StorageServiceInterface::class);
        $storageService->method('getTempStorage')->willReturn($this->storage);

        return $storageService;
    }

    private function readBack(int $id): Spreadsheet
    {
        $file = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($file, $this->storage->read(ImageEmbeddingXlsxExportService::filePath($id)));

        try
        {
            return (new XlsxReader())->load($file);
        }
        finally
        {
            unlink($file);
        }
    }
}
