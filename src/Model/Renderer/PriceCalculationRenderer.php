<?php

namespace App\Model\Renderer;

use App\OptionProvider\PriceLevelProvider;
use App\Service\PricingService;
use InvalidArgumentException;
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

        $validmode = ['base', 'fixed', 'drop', 'hurt'];
        $mergedModes = implode(', ', $validmode);
        $validtimings = ['present', 'future'];
        $mergedTimings = implode(', ', $validtimings);

        $args = explode('|', $data);
        if(count($args) != 2)
        {
            return '<div class="alert alert-danger">Unvalid data passed to renderer. Please fix in class definition as {'.$mergedModes.'}|{'.$mergedTimings.'}). [' . $data . '] provided.</div>';
        }

        $mode = $args[0];
        $timing = $args[1];

        if(!in_array($mode, $validmode))
        {
            return '<div class="alert alert-danger">Unsuported mode ' . $mode . '. Please use one of: ' . $mergedModes . '</div>';
        }

        if(!in_array($timing, $validtimings))
        {
            return '<div class="alert alert-danger">Unsuported timing ' . $timing . '. Please use one of: ' . $mergedTimings . '</div>';
        }

        if($timing == 'present' && (!$object->getPrice_base() || $object->getPrice_base()->getValue() <= 0))
        {
            return '<div class="alert alert-warning">Product base price is not provided.</div>';
        }

        if($timing == 'future' && (!$object->getPrice_new_base() || $object->getPrice_new_base()->getValue() <= 0))
        {
            return '<div class="alert alert-warning">Product new base price is not provided.</div>';
        }

        $basePriceGetter = 'get' . ($timing == 'present' ? 'Price_base' : 'Price_new_base');

        if($mode == 'base')
        {
            return $this->getBasePrices($data, $object, $params, $basePriceGetter);
        }

        if($mode == 'fixed')
        {
            return $this->getFixedPrices($data, $object, $params, $basePriceGetter);
        }

        if($mode == 'drop')
        {
            return $this->getRegularDrops($data, $object, $params, 30, 45, $timing);
        }

        if ($mode == 'hurt')
        {
            return $this->getRegularDrops($data, $object, $params, 45, 55, $timing);
        }

        return 'unknown';
    }

    private function getBasePrices(string $data, Concrete $object, $params, $basePriceGetter)
    {
        $base = $object->{$basePriceGetter}()->getValue();
        $catalogRawPLN = round($base * 3, 2);
        $roundedPLN = $this->prettyRoundPrice($catalogRawPLN);

        $eurFactor = Unit::getById('EUR')->getFactor();
        $eur = round($roundedPLN / ($eurFactor * 0.97), 2);

        $pln = "<h2>Cena katalogowa PLN</h2> <h3>{$roundedPLN}</h3><br/>= {$base} * 3.000 = {$catalogRawPLN}";
        $eur = "<h2>Cena katalogowa EUR</h2> <h3>{$eur}</h3><br/>= $roundedPLN / ($eurFactor * 0.97)";

        $ret = "$pln<br/>$eur";

        $ret = '<div class="alert alert-info">' . $pln . '</div>';
        $ret .= '<div class="alert alert-info">' . $eur . '</div>';

        return $ret;
    }

    private function getFixedPrices(string $data, Product|ProductSet $object, array $params, string $basePriceGetter)
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

        $base = $object->{$basePriceGetter}()->getValue();

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

    private function getRegularDrops(string $data, Product|ProductSet $product, array $params, int $start, int $end, string $timing): string
    {
        if($timing == 'present')
        {
            $basePriceGetter = 'getPrice_base';
            $catalogPlnGetter = 'getPrice_catalog_pln';
            $catalogEurGetter = 'getPrice_catalog_eur';
        }
        else
        {
            $basePriceGetter = 'getPrice_new_base';
            $catalogPlnGetter = 'getPrice_new_catalog_pln';
            $catalogEurGetter = 'getPrice_new_catalog_eur';
        }


        if (!$product->{$catalogPlnGetter}() || $product->{$catalogPlnGetter}()->getValue() <= 0) {
            return '<div class="alert alert-danger">Product catalog PLN price is not provided.</div>';
        }

        if (!$product->{$catalogEurGetter}() || $product->{$catalogEurGetter}()->getValue() <= 0) {
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

        $pln = $product->{$catalogPlnGetter}()->getValue();
        $eur = $product->{$catalogEurGetter}()->getValue();
        $base = $product->{$basePriceGetter}()->getValue();


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

    public function prettyRoundPrice(float $price): float
    {
        if ($price < 3) {
            return $this->ceilToStep($price, 0.1);
        } elseif ($price < 10) {
            return $this->ceilToStep($price, 0.5);
        } elseif ($price < 50) {
            return $this->ceilToStep($price, 1);
        } elseif ($price < 100) {
            return $this->ceilToStep($price, 5);
        } elseif ($price < 1_000) {
            return $this->ceilToStep($price, 10) - 1;
        } elseif ($price < 1_100) {
            return 1_099;
        } elseif ($price < 2_000) {
            return $this->ceilToStep($price, 50) - 1;
        } elseif ($price < 2_100) {
            return 2_099;
        } elseif ($price < 3_000) {
            return $this->ceilToStep($price, 50) - 1;
        } elseif ($price < 3_100) {
            return 3_099;
        }

        return $this->ceilToStep($price, 100) - 1;
    }

    private function ceilToStep(float $value, float $step): float
    {
        if ($step <= 0) {
            throw new InvalidArgumentException('Step must be greater than zero.');
        }

        return ceil($value / $step) * $step;
    }
}
