<?php

namespace App\Command;

use App\Service\PriceLevelService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Product;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:"prices:switch", description: "Switch prices based on established date")]
class SwitchPricesCommand extends AbstractCommand
{
    public function __construct(private readonly PriceLevelService $priceLevelService)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument("type", InputArgument::REQUIRED, "Switch type");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument("type") ?? null;

        if($type && $type == 'base')
        {
            $products = new Product\Listing();
            $products->setUnpublished(true);
            $ids = $products->loadIdList();

            $TOTAL = count($ids);
            $BATCH_SIZE = 100;
            $i = 0;

            while($i < $TOTAL)
            {

                try
                {
                    $p = Product::getById($ids[$i]);
                    $p->setprice_base($p->getBasePrice());
                    $p->save();
                }
                catch(\Throwable $e)
                {
                    $this->writeError($e->getMessage());
                }

                $this->writeInfo("[~] #{$p->getId()}, {$p->getKey()}");

                $i++;

                if($i % $BATCH_SIZE === 0)
                {
                    \Pimcore::collectGarbage();
                    $this->writeInfo("--- Garbage collected ---");
                }
            }
        }

        $priceDates = $this->priceLevelService->getPriceLevelValidDates();
        $conditions = [];

        $dates = [];

        foreach($priceDates as $priceDate)
        {
            $dateName = $priceDate['value'];
            $conditions[] = "`{$dateName}` <= CURRENT_TIMESTAMP";

            $dates[] = $dateName;
        }

        $condition = implode(" OR ", $conditions);

        $products = new Product\Listing();
        $products->setCondition($condition);
        $products->setUnpublished(true);

        $now = new \DateTime();

        /** @var Product $product */
        foreach($products as $product)
        {
            $this->writeInfo("Processing {$product->getKey()}");
            $saved = false;

            foreach($dates as $date)
            {
                $newPriceField = str_replace('date_price_new', 'price_new', $date);
                $priceField = str_replace('date_price_new', 'price', $date);
                $dateField = $date;

                /** @var QuantityValue $newPrice */
                $newPrice = $product->{"get" . $newPriceField}();

                /** @var QuantityValue $oldPrice */
                $oldPrice = $product->{"get" . $priceField}();

                $since = $product->{"get" . $date}();

                if($since and $since < $now)
                {
                    $old = $oldPrice?->getValue() ?? 0;
                    $new = $newPrice?->getValue() ?? 0;

                    $setterNew = "set" . $newPriceField;
                    $setter = "set" . $priceField;
                    $setterDate = "set" . $date;

                    if($new === 0)
                    {
                        $product->{$setter}(null);
                        $product->{$setterNew}(null);
                        $product->{$setterDate}(null);
                    }
                    else
                    {
                        $product->{$setter}($newPrice);
                        $product->{$setterNew}(null);
                        $product->{$setterDate}(null);
                    }

                    $saved = true;
                }
            }

            if($saved)
            {
                $product->save(['versionNote' => 'Price update']);
            }
        }

        return Command::SUCCESS;
    }
}
