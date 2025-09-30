<?php

namespace App\Command;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand('stocks:update', "Update product stock")]
class UpdateStockCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('object_id', InputArgument::REQUIRED, "product or productset id");
        $this->addArgument('in_stock', InputArgument::REQUIRED, "quantity of stock");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('object_id');
        $obj = DataObject::getById($id);

        if(!($obj instanceof Product || $obj instanceof ProductSet))
        {
            $output->writeln("<error>Product or product set not found</error>");
            return Command::FAILURE;
        }

        $newStock = (int)$input->getArgument('in_stock');
        if($newStock < 0)
        {
            $output->write("<error>In-stock quantity must be greater than 0</error>");
            return Command::FAILURE;
        }

        $obj->setStock($input->getArgument('in_stock'));

        $ss = $obj->getStock();
        $output->writeln("<info>Stock updated successfully: {$ss}</info>");

        return Command::SUCCESS;
    }
}
