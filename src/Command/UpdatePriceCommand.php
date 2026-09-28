<?php

namespace App\Command;

use App\Service\PriceLevelService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:"prices:update", description: "Update future prices")]
class UpdatePriceCommand extends AbstractCommand
{
    public function __construct(private readonly PriceLevelService $priceLevelService)
    {
        parent::__construct();
    }

    public function configure()
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->writeInfo("Loading id list...");

        $products = new Product\Listing();
        $products->setUnpublished(true);
        $ids = $products->loadIdList();

        $TOTAL = count($ids);
        $BATCH_SIZE = 100;
        $i = 0;

        while($i < $TOTAL)
        {
            $p = Product::getById($ids[$i]);
            $this->writeInfo("[$i / $TOTAL]");

            try
            {
                if(!$p->getPrice_new_base() || !$p->getPrice_new_base()->getValue())
                {
                    continue;
                }

                $base = $p->getPrice_new_base()->getValue();
                $catalogRawPLN = round($base * 3, 2);
                $pln = $this->priceLevelService->prettyRoundPrice($catalogRawPLN);

                $eurFactor = Unit::getById('EUR')->getFactor();
                $eur = ceil($pln / ($eurFactor * 0.97));

                $p->setPrice_new_catalog_pln(new QuantityValue($pln, Unit::getById('PLN')));
                $p->setPrice_new_catalog_eur(new QuantityValue($eur, Unit::getById('EUR')));

                $p->save();
            }
            catch(\Throwable $e)
            {
                $this->writeError($e->getMessage());
            } finally {

                $this->writeInfo("[~] #{$p->getId()}, {$p->getKey()}");

                $i++;

                if($i % $BATCH_SIZE === 0)
                {
                    \Pimcore::collectGarbage();
                    $this->writeInfo("--- Garbage collected ---");
                }
            }
        }


        return Command::SUCCESS;
    }
}
