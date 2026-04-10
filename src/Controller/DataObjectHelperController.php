<?php

declare(strict_types=1);

namespace App\Controller;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Pimcore\Bundle\AdminBundle\Helper\GridHelperService;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Tool\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/bnix", name: "bnix")]
class DataObjectHelperController extends UserAwareController
{
    use JsonHelperTrait;

    #[Route("/download-xlsx-file", name: "_download_xlsx_with_images", options: ['expose' => true])]
    public function downloadXlsxWithImages(Request $request, GridHelperService $gridHelperService): BinaryFileResponse
    {
        $storage = Storage::get('temp');
        $fileHandle = File::getValidFilename($request->get('fileHandle'));
        $csvFile = $this->getCsvFile($fileHandle);

        try {
            return $this->createXlsxExportFile($storage, $fileHandle, $csvFile);
        } catch (\Exception | FilesystemException | UnableToReadFile $exception) {
            // handle the error
            throw $this->createNotFoundException('XLSX file not found');
        }
    }

    /**
     * @throws FilesystemException
     * @throws Exception|\PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function createXlsxExportFile(FilesystemOperator $storage, string $fileHandle, string $csvFile): BinaryFileResponse
    {
        $IMAGE_SIZE = 80;
        $ROW_HEIGHT = 64;
        $CELL_WIDTH = 12;
        $THUMBNAIL_NAME = "100x100";

        $csvReader = new Csv();
        $csvReader->setDelimiter(';');
        $csvReader->setSheetIndex(0);

        $spreadsheet = $csvReader->loadSpreadsheetFromString($storage->read($csvFile));

        $ws = $spreadsheet->getActiveSheet();

        $columnWithImages = [];

        foreach($ws->getRowIterator() as $row) {
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);

            /** @var Cell $cell */
            foreach ($cells as $cell) {
                $value = $cell->getValue();

                if(is_string($value)) {
                    if (preg_match('/!\[.*?\]\((.*?)\)/', $value, $matches)) {

                        $columnWithImages[$cell->getColumn()] = true;

                        $imagePath = $matches[1];
                        $im = Asset::getByPath($imagePath);

                        if($im) {
                            $image = $im->getThumbnail($THUMBNAIL_NAME);

                            $stream = $image->getStream();

                            $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_');
                            file_put_contents($tempFile, stream_get_contents($stream));

                            if (file_exists($tempFile)) {
                                $cell->setValue(null);

                                $drawing = new Drawing();
                                $drawing->setPath($tempFile);
                                $drawing->setHeight($IMAGE_SIZE); // Set image height (adjust as needed)
                                $drawing->setCoordinates($cell->getCoordinate()); // Place image in column D
                                $drawing->setWorksheet($ws);

                                $ws->getRowDimension($row->getRowIndex())->setRowHeight($ROW_HEIGHT);
                            }
                        }
                    }
                    else
                    {
                        $columnWithImages[$cell->getColumn()] = false;
                    }
                }
            }
        }

        foreach($columnWithImages as $column => $isImage) {
            if($isImage) {
                $ws->getColumnDimension($column)->setWidth($CELL_WIDTH);
            }else{
                $ws->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $xlsxFilename = PIMCORE_SYSTEM_TEMP_DIRECTORY. '/' .$fileHandle. '.xlsx';
        $writer->save($xlsxFilename);

        $response = new BinaryFileResponse($xlsxFilename);
        $response->headers->set('Content-Type', 'application/xlsx');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'export-' . date('d-m-Y') . '.xlsx');
        $response->deleteFileAfterSend(true);

        $storage->delete($csvFile);

        return $response;
    }

    protected function getCsvFile(string $fileHandle): string
    {
        return $fileHandle . '.csv';
    }
}
