<?php

namespace App\Service;

use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use Pimcore\Helper\Mail;
use Psr\Http\Client\ClientInterface;

/**
 * Renders HTML to PDF with Gotenberg (Chromium). Replaces WebToPrintBundle's Processor::getPdfFromString()
 * and accepts the same params: paperWidth, paperHeight, margin*, landscape, printBackground, scale, metadata, ...
 */
class GotenbergPdfService
{
    private const DEFAULT_PARAMS = [
        'printBackground' => true,
        'landscape' => false,
    ];

    private const FLAG_OPTIONS = [
        'printBackground', 'landscape', 'preferCssPageSize', 'omitBackground', 'emulatePrintMediaType', 'emulateScreenMediaType',
    ];

    public function __construct(private readonly string $baseUrl, private readonly string $assetHostUrl, private readonly ?ClientInterface $httpClient = null)
    {
    }

    public function getPdfFromString(string $html, array $params = []): string
    {
        // Gotenberg fetches images/css itself, so relative paths must point to a host reachable from its container
        $html = Mail::setAbsolutePaths($html, null, $this->assetHostUrl);

        $params = $params ?: self::DEFAULT_PARAMS;

        $chromium = Gotenberg::chromium($this->baseUrl)->pdf();

        foreach (self::FLAG_OPTIONS as $option)
        {
            if (!empty($params[$option]))
            {
                $chromium->$option();
            }
        }

        $chromium->margins(
            $params['marginTop'] ?? 0.39,
            $params['marginBottom'] ?? 0.39,
            $params['marginLeft'] ?? 0.39,
            $params['marginRight'] ?? 0.39
        );

        if (isset($params['paperWidth']) || isset($params['paperHeight']))
        {
            $chromium->paperSize($params['paperWidth'] ?? 8.5, $params['paperHeight'] ?? 11);
        }

        if (isset($params['scale']))
        {
            $chromium->scale($params['scale']);
        }

        if (isset($params['nativePageRanges']))
        {
            $chromium->nativePageRanges($params['nativePageRanges']);
        }

        if (isset($params['metadata']))
        {
            $chromium->metadata($params['metadata']);
        }

        $request = $chromium->html(Stream::string('index.html', $html));

        return Gotenberg::send($request, $this->httpClient)->getBody()->getContents();
    }
}
