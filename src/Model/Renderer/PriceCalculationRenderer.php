<?php

namespace App\Model\Renderer;

use App\OptionProvider\PriceLevelProvider;
use App\Service\PricingService;
use Pimcore\Model\DataObject\ClassDefinition\Layout\DynamicTextLabelInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;


class PriceCalculationRenderer implements DynamicTextLabelInterface
{
    public function renderLayoutText(string $data, ?Concrete $object, array $params): string
    {
        if(!($object instanceof Product))
        {
            return '<div class="alert alert-danger">Unsuported object type</div>';
        }

        if(!$object->getBase() || $object->getBase()->getValue() <= 0)
        {
            return '<div class="alert alert-warning">Product base price is not provided.</div>';
        }

        if(!empty($data))
        {
            if($data == 'dropshipping')
            {
                return $this->getRegularDrops($data, $object, $params, 30, 45);
            }
            else if ($data == 'hurt')
            {
                return $this->getRegularDrops($data, $object, $params, 45, 55);
            }
            else
            {
                return 'unknown';
            }
        }

        return $this->getFixedPrices($data, $object, $params);
    }

    private function getFixedPrices(string $data, Product|ProductSet $object, array $params)
    {
        $factors = [
            'price_custom_cama' => 1.31,
            'price_custom_mirjan' => 1.30,
            'price_custom_agata' => 1.35,
            'price_custom_selsey' => 1.35,
            'price_custom_lectus' => 1.37,
            'price_custom_furnidea' => 1.38,
            'price_custom_bogart' => 1.45,
            'price_custom_uptrend' => 1.32,
            'price_custom_lomado' => 1.38,
            'price_custom_vente' => 1.34,
        ];

        $eurFactors = ['price_custom_lomado', 'price_custom_vente'];

        $provider = new PriceLevelProvider();

        $def = new ClassDefinition\Data\Select();
        $def->setOptionsProviderData("price_custom_");

        $levelsRaw = $provider->getOptions([], $def);
        $levels= [];
        foreach ($levelsRaw as $item)
        {
            $levels[$item['value']] = $item['key'];
        }

        $data = "<h2>Kalkulacja cen indywidualnych</h2>
<style>
table {
border-collapse: collapse;
}

table tr td {
border: 1px solid #000;
padding: 3px;
text-align: center;
}

table tr td:first-child
{
text-align: left;
}

table thead tr td {
    background-color: #e4e4e4;
}
</style>

<table>
<thead>
<tr>
<td>Klient</td>
<td>Waluta</td>
<td>Aktualny kurs</td>
<td>Narzut</td>
<td>Cena domyślna</td>
<td><strong>Aktualna cena</strong></td>
<td>Aktualny narzut</td>
<td>Aktualny zysk PLN</td>
</tr></thead>
<tbody>";

        $base = $object->getBase()->getValue();

        foreach ($levels as $level => $levelName) {

            $factor = $factors[$level];

            $getter = 'get' . $level;

            $currency = in_array($level, $eurFactors) ? 'EUR' : 'PLN';
            $currencyUnit = Unit::getById($currency);

            $rate = $currency != 'PLN' ? $currencyUnit->getFactor() : 1.0;

            $defaultPrice = round($factor * $base / $rate, 2);
            $actualPrice = $object->{$getter}()?->getValue() ?? 0;

            $actualFactor = ($actualPrice) ? round($actualPrice * $rate / $base, 2) : null;
            $actualProfit = ($actualPrice) ? round($actualPrice * $rate - $base, 2) : null;

            $css = "";
            if($actualPrice > 0 && $actualProfit <= 0)
            {
                $css = "color: #ff0000; font-weight: bold";
            }
            else if($actualPrice > 0)
            {
                $css = "font-weight: bold;";
            }

            $data .= "<tr style='" . $css . "'>
                        <td>" . $levelName . "</td>
                        <td>" . $currency . "</td>
                        <td>" . ($rate == 1 ? '' : number_format($rate, 4)) . "</td>
                        <td>" . number_format($factor, 3) ."</td>
                        <td>" . number_format($defaultPrice, 2, ',', ' ') . "</td>
                        <td>" . (number_format($actualPrice, 2, ',', ' ') ?? '') . "</td>
                        <td>" . (number_format($actualFactor, 2, ',', ' ') ?? '') . "</td>
                        <td>" . (number_format($actualProfit, 2, ',', ' ') ?? '') . "</td>
                    </tr>";
        }

        $data .="
</tbody>
</table>";

        return $data;
    }

    private function getRegularDrops(string $data, Product|ProductSet $product, array $params, int $start, int $end): string
    {
        if (!$product->getPrice_catalog_pln() || $product->getPrice_catalog_pln()->getValue() <= 0) {
            return '<div class="alert alert-danger">Product catalog PLN price is not provided.</div>';
        }

        if (!$product->getPrice_catalog_eur() || $product->getPrice_catalog_eur()->getValue() <= 0) {
            return '<div class="alert alert-danger">Product catalog EUR price is not provided.</div>';
        }

        $html = "<h2>Kalkulacja rabatów od ceny katalogowej</h2>
<style>
table {
border-collapse: collapse;
}

table tr td {
border: 1px solid #000;
padding: 3px;
text-align: center;
}

table tr td:first-child
{
text-align: left;
}
</style>

<table>
<thead>
<tr>
<td>Rabat</td>
<td><strong>Cena PLN po rabacie</strong></td>
<td>Zysk PLN</td>
<td><strong>Cena EUR po rabacie</strong></td>
<td>Zysk PLN</td>
</tr></thead>
<tbody>";

        $pln = $product->getPrice_catalog_pln()->getValue();
        $eur = $product->getPrice_catalog_eur()->getValue();
        $base = $product->getBase()->getValue();


        for($i = $start; $i <= $end; $i++)
        {
            $pricePLN = round($pln * (100 - $i) / 100, 2);
            $profitPLN = $pricePLN - $base;

            $eurUnit = Unit::getById('EUR');
            $eurRate = $eurUnit->getFactor();

            $priceEUR = round($eur * (100 - $i) / 100, 2);
            $profitEUR = $priceEUR * $eurRate - $base;

            $cssPLN = $profitPLN <= 0 ? "color: #ff0000; font-weight: bold" : "";
            $cssPLN = $profitEUR <= 0 ? "color: #ff0000; font-weight: bold" : "";

            $html .= "<tr style='$cssPLN'>
                        <td>{$i}%</td>
                        <td>" . number_format($pricePLN, 2, ',', ' ') . "</td>
                        <td>" . number_format($profitPLN, 2, ',', ' ') . "</td>
                        <td>" . number_format($priceEUR, 2, ',', ' ') . "</td>
                        <td>" . number_format($profitEUR, 2, ',', ' ') . "</td>
                    </tr>";
        }

        return $html;
    }
}
