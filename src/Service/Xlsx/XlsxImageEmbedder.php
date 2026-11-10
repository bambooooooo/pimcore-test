<?php

declare(strict_types=1);

namespace App\Service\Xlsx;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\ImageGallery;

/**
 * Replaces image references in an exported spreadsheet with embedded thumbnails.
 *
 * Recognised cell values (as written by grid exports):
 *  - an asset path of an image, e.g. "/Products/chair.jpg" (image field)
 *  - a base64 serialized ImageGallery (image gallery field)
 *  - markdown images "![label](/path.jpg)" (legacy classic admin export)
 */
class XlsxImageEmbedder
{
    public const IMAGE_SIZE = 80;
    public const ROW_HEIGHT = 64;
    public const CELL_WIDTH = 12;
    public const THUMBNAIL = '100x100';

    /** @var callable(string): ?string returns a local file with the thumbnail of the image at the given path */
    private $thumbnailFile;

    /** @var string[] */
    private array $tempFiles = [];

    public function __construct(?callable $thumbnailFile = null)
    {
        $this->thumbnailFile = $thumbnailFile ?? $this->createThumbnailFile(...);
    }

    public function __destruct()
    {
        $this->cleanUp();
    }

    /**
     * @return int number of embedded images
     */
    public function embed(Spreadsheet $spreadsheet): int
    {
        $embedded = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $ws)
        {
            $imageColumns = [];

            foreach ($ws->getRowIterator() as $row)
            {
                $cells = $row->getCellIterator();
                $cells->setIterateOnlyExistingCells(true);

                foreach ($cells as $cell)
                {
                    $paths = $this->extractImagePaths($cell->getValue());

                    if ($paths === null)
                    {
                        continue;
                    }

                    $cell->setValue(null);
                    $i = 0;

                    foreach ($paths as $path)
                    {
                        if (!$file = ($this->thumbnailFile)($path))
                        {
                            continue;
                        }

                        $drawing = new Drawing();
                        $drawing->setPath($file);
                        $drawing->setHeight(self::IMAGE_SIZE);
                        $drawing->setCoordinates($cell->getCoordinate());
                        $drawing->setOffsetX(self::IMAGE_SIZE * $i);
                        $drawing->setWorksheet($ws);

                        $i++;
                        $embedded++;
                    }

                    if ($i > 0)
                    {
                        $ws->getRowDimension($row->getRowIndex())->setRowHeight(self::ROW_HEIGHT);
                        $imageColumns[$cell->getColumn()] = max($imageColumns[$cell->getColumn()] ?? 1, $i);
                    }
                }
            }

            foreach ($imageColumns as $column => $imageCount)
            {
                $ws->getColumnDimension($column)->setWidth(self::CELL_WIDTH * $imageCount);
            }
        }

        return $embedded;
    }

    /**
     * @return string[]|null image paths, or null when the value is not an image reference
     */
    public function extractImagePaths(mixed $value): ?array
    {
        if (!is_string($value) || $value === '')
        {
            return null;
        }

        if (preg_match_all('/!\[[^\]]*]\(([^)]+)\)/', $value, $matches))
        {
            return $matches[1];
        }

        if ($value === '[]')
        {
            return [];
        }

        if (($gallery = $this->extractImageGalleryPaths($value)) !== null)
        {
            return $gallery;
        }

        if ($value[0] === '/' && preg_match('/\.(jpe?g|png|gif|webp|tiff?|bmp|svg)$/i', $value))
        {
            return [$value];
        }

        return null;
    }

    public function cleanUp(): void
    {
        foreach ($this->tempFiles as $file)
        {
            @unlink($file);
        }

        $this->tempFiles = [];
    }

    private function extractImageGalleryPaths(string $value): ?array
    {
        $decoded = base64_decode($value, true);

        if (!$decoded || !preg_match('/^(O|a):/', $decoded))
        {
            return null;
        }

        try
        {
            $data = unserialize($decoded, [
                'allowed_classes' => [ImageGallery::class, Hotspotimage::class, Asset\Image::class],
            ]);
        }
        catch (\Throwable)
        {
            return null;
        }

        if (!$data instanceof ImageGallery)
        {
            return null;
        }

        $paths = [];

        foreach ($data->getItems() ?? [] as $item)
        {
            if ($item instanceof Hotspotimage && ($image = $item->getImage()) instanceof Asset\Image)
            {
                $paths[] = $image->getFullPath();
            }
        }

        return $paths;
    }

    private function createThumbnailFile(string $path): ?string
    {
        $image = Asset\Image::getByPath($path);

        if (!$image instanceof Asset\Image)
        {
            return null;
        }

        $stream = $image->getThumbnail(self::THUMBNAIL)->getStream();

        if (!$stream)
        {
            return null;
        }

        $file = tempnam(sys_get_temp_dir(), 'pim_image_');
        file_put_contents($file, $stream);
        $this->tempFiles[] = $file;

        return $file;
    }
}
