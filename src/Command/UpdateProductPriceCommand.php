<?php

declare(strict_types=1);

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'bamboo:prices:update',
    description: 'Recalculate all product prices as sum of packages base prices'
)]
class UpdateProductPriceCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        DataObject::setHideUnpublished(false);
        $prods = new DataObject\Product\Listing();

        $PLN = DataObject\QuantityValue\Unit::getById("PLN");

        foreach ($prods as $prod)
        {
            $key = $prod->getKey();

            $baseprice = 0.0;

            if(!$prod->getPackages())
            {
                continue;
            }

            foreach($prod->getPackages() as $lip)
            {
                if(!$lip->getElement()->getBasePrice())
                {
                    continue;
                }

                if(!$lip->getQuantity())
                {
                    continue;
                }

                $baseprice += $lip->getElement()->getBasePrice()->getValue() * $lip->getQuantity();
            }

            if(!$prod->getBasePrice() || ($prod->getBasePrice()->getValue() != $baseprice))
            {
                $price = $prod->getBasePrice() ?? "-";

                if($baseprice > 0)
                {
                    $this->writeInfo("[~] $key: $price => $baseprice");

                    $bp  = new DataObject\Data\QuantityValue($baseprice, $PLN);
                    $prod->setBasePrice($bp);
                    $prod->save();
                }
            }
        }

        return self::SUCCESS;
    }
}
