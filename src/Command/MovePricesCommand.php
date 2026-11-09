<?php

namespace App\Command;

use App\Service\PriceLevelService;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:"prices:restore-old", description: "Move old prices to new fixed structure (BC)")]
class MovePricesCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument("class", InputArgument::OPTIONAL, "Product(Set) class", 'Product');
        $this->addArgument("offset", InputArgument::OPTIONAL, "Id offset", 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $MAP = [
            4582 => 'price_drop23',
            4583 => 'price_drop26',
            4584 => 'price_drop29',
            5167 => 'price_hurt32',
            4619 => 'price_dropeu3',
            4620 => 'price_dropeu12',
            4621 => 'price_dropeu17',
            4627 => 'price_hurteu20',
            4585 => 'price_sugerowana'
        ];

        $pln = Unit::getById('PLN');
        $eur = Unit::getById('EUR');

        $class = $input->getArgument("class");
        $offset = (int)$input->getArgument("offset");

        if(!($class == 'Product' || $class == 'ProductSet'))
        {
            $this->writeError("Class '{$class}' not supported");
            return Command::FAILURE;
        }

        $this->writeInfo("Loading id list...");

        $items = ($class == 'Product') ? (new Product\Listing()) : (new ProductSet\Listing()); ;
        $items->setUnpublished(true);
        $ids = $items->loadIdList();
        sort($ids);

        $this->writeInfo("Found " . count($ids) . " items.");
        $ids = array_slice($ids, $offset);

        $this->writeInfo("Starting from offset: " . $offset . " (" . count($ids) . " items).");

        $TOTAL = count($ids);
        $BATCH_SIZE = 25;
        $i = 0;

        while($i < $TOTAL)
        {
            /** @var Product|ProductSet $obj */
            $obj = DataObject::getById($ids[$i]);

            try
            {
                foreach($obj->getPrice() as $p)
                {
                    if(array_key_exists($p->getObjectId(), $MAP))
                    {
                        $price = (float)$p->getPrice();
                        $setter = "set" . ucfirst($MAP[$p->getObjectId()]);
                        $currency = str_contains($setter, 'eu') ? $eur : $pln;

                        $obj->{$setter}(new QuantityValue($price, $currency));
                    }
                }

                $obj->save(['versionNote' => 'Restore old price level']);
            }
            catch(\Throwable $e) {
                $this->writeError($e->getMessage());
            } finally {

                $this->writeInfo("[~][$i / $TOTAL] #{$obj->getId()}, {$obj->getKey()}");
                $i++;
            }
        }

        return Command::SUCCESS;
    }
}
