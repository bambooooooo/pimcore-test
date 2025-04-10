<?php

namespace App\Service;

use InvalidArgumentException;
use Pimcore\Model\DataObject\EanPool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GS1Service
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
        $this->httpClient = $this->httpClient->withOptions([
            'auth_basic' => ['151773', 'f9c0c5e7a859330827cb7509db696975']
        ]);
    }

    public function updateFreeCodes(EanPool $eanPool) : void
    {
        $allEans = $this->getValidEans13WithPrefix($eanPool->getPrefix());

        try {
            $response = $this->httpClient->request('GET', 'https://mojegs1.pl/api/v2/products', [
                'query' => [
                    'filter[keyword]' => $eanPool->getPrefix(),
                    'page[offset]' => 1,
                    'page[limit]' => 100,
                    'sort' => 'gtin'
                ]
            ]);

            $data = $response->toArray();

            $taken = [];

            foreach ($data['data'] as $row) {
                $taken[] = ltrim($row['id'], '0');
            }

            while($data['links']['next'] != null)
            {
                $response = $this->httpClient->request('GET', $data['links']['next']);

                $data = $response->toArray();

                foreach ($data['data'] as $row) {
                    $taken[] = ltrim($row['id'], '0');
                }
            }

            $free = array_values(array_diff($allEans, $taken));

            $pimTable = [];
            foreach ($free as $ean) {
                $pimTable[] = [$ean];
            }

            $eanPool->setAvailableCodes($pimTable);
            $eanPool->save();
        }
        catch (\Exception $e) {
            dump($e);
        }
    }

    function getValidEans13WithPrefix($suffix) : array
    {
        if(strlen($suffix) > 12 or strlen($suffix) <= 1) {
            throw new InvalidArgumentException('Invalid suffix');
        }

        $output = [];

        $idPartLength = 13 - strlen($suffix) - 1;
        $limit = (int)pow(10, $idPartLength);

        for($i = 0; $i < $limit; $i++) {
            $idPart = str_pad($i, $idPartLength, '0', STR_PAD_LEFT);
            $ean12 = $suffix . $idPart;
            $ean13 = $ean12 . $this->calculateEAN13CheckDigit($ean12);

            $output[] = $ean13;
        }

        return $output;
    }

    function calculateEAN13CheckDigit(string $ean12): string {
        if (!preg_match('/^\d{12}$/', $ean12)) {
            throw new InvalidArgumentException("Input must be a 12-digit numeric string.");
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$ean12[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return (string)$checkDigit;
    }
}
