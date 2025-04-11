<?php

declare(strict_types=1);

namespace App\Command;

use Pimcore\Model\Asset\MetaData\ClassDefinition\Data\DataObject;
use Pimcore\Model\DataObject\Product;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'bamboo:dev',
    description: 'dev command'
)]
class CustomCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dummy();

        return Command::SUCCESS;
    }

    private function dummy() : void
    {

    }
}
