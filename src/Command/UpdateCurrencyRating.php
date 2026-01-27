<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name:"currency:update-exchange-rate", description: "Update currency rating based on NBP bank data")]
class UpdateCurrencyRating extends AbstractCommand
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateCurrency('EUR');
        $this->updateCurrency('USD');
        $this->updateCurrency('GBP');

        return Command::SUCCESS;
    }

    private function updateCurrency($code)
    {
        $NBP_API_URL = 'https://api.nbp.pl/api';
        $TABLE = 'A';

        $url = "$NBP_API_URL/exchangerates/rates/$TABLE/$code?format=json";
        $res = $this->httpClient->request('GET', $url)->toArray();
        $rate = $res['rates'][0]['mid'];

        $currency = Unit::getById($code);
        $this->writeInfo("$code: " . $currency->getFactor() . " => " . $rate);

        $currency->setFactor($rate);
        $currency->save();
    }
}
