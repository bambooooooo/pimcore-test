<?php

declare(strict_types=1);

namespace App\Studio\Export;

use App\Service\Xlsx\XlsxImageEmbedder;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Pimcore\Bundle\StudioBackendBundle\Element\Service\StorageServiceInterface;
use Pimcore\Bundle\StudioBackendBundle\Export\Model\GridExportData;
use Pimcore\Bundle\StudioBackendBundle\Export\Service\ExportServiceInterface;
use Pimcore\Bundle\StudioBackendBundle\Export\Util\Constant\ExportFile;
use Pimcore\Model\UserInterface;

/**
 * Decorates the Pimcore Studio XLSX grid export: image columns get embedded thumbnails instead of asset paths.
 * Successor of the classic admin "XLSX Export (Images)" grid button.
 */
final class ImageEmbeddingXlsxExportService implements ExportServiceInterface
{
    public function __construct(
        private readonly ExportServiceInterface $inner,
        private readonly StorageServiceInterface $storageService,
        private readonly XlsxImageEmbedder $embedder,
    ) {
    }

    public function createExportFile(int $id, GridExportData $gridExportData, UserInterface $user, ?string $delimiter = null): void
    {
        $this->inner->createExportFile($id, $gridExportData, $user, $delimiter);

        $storage = $this->storageService->getTempStorage();
        $path = self::filePath($id);

        if (!$storage->fileExists($path))
        {
            return;
        }

        $local = tempnam(sys_get_temp_dir(), 'pim_xlsx_');

        try
        {
            file_put_contents($local, $storage->readStream($path));
            $spreadsheet = (new XlsxReader())->load($local);

            if ($this->embedder->embed($spreadsheet) === 0)
            {
                return;
            }

            (new XlsxWriter($spreadsheet))->save($local);
            $storage->writeStream($path, fopen($local, 'rb'));
        }
        finally
        {
            $this->embedder->cleanUp();
            @unlink($local);
        }
    }

    public function cleanUpFileSystem(int $jobRunId, string $folderName, string $fileName): void
    {
        $this->inner->cleanUpFileSystem($jobRunId, $folderName, $fileName);
    }

    public static function filePath(int $id): string
    {
        $name = static fn (ExportFile $f): string => str_replace('{id}', (string)$id, $f->value);

        return $name(ExportFile::XLSX_FOLDER_NAME) . '/' . $name(ExportFile::XLSX_FILE_NAME);
    }
}
