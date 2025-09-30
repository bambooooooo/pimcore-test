<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("pimcore:deployment:post-update", "Scripts to run after update")]
class PostUpdateCommand extends AbstractCommand
{
    public function __construct(private readonly int $systemCsId, private readonly int $allegroCsId)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $PARAMETERS = "Parameters";
        $PARAMETERS_ALLEGRO = "ParametersAllegro";

        $p = ClassDefinition::getById("Product");
        $s = ClassDefinition::getById("ProductSet");

        $fdP = $p->getFieldDefinitions();
        $fdS = $s->getFieldDefinitions();

        if(key_exists($PARAMETERS, $fdP)) {
            $this->writeInfo("Product.$PARAMETERS: " . $fdP[$PARAMETERS]->getStoreId() . " => " . $this->systemCsId);
            $fdP[$PARAMETERS]->setStoreId($this->systemCsId);
        }

        if(key_exists($PARAMETERS_ALLEGRO, $fdP)) {
            $this->writeInfo("Product.$PARAMETERS_ALLEGRO: " . $fdP[$PARAMETERS_ALLEGRO]->getStoreId() . " => " . $this->allegroCsId);
            $fdP[$PARAMETERS_ALLEGRO]->setStoreId($this->allegroCsId);
        }

        $p->save();

        if(key_exists($PARAMETERS, $fdS)) {
            $this->writeInfo("ProductSet.$PARAMETERS: " . $fdS[$PARAMETERS]->getStoreId() . " => " . $this->systemCsId);
            $fdS[$PARAMETERS]->setStoreId($this->systemCsId);
        }

        if(key_exists($PARAMETERS_ALLEGRO, $fdS)) {
            $this->writeInfo("Product.$PARAMETERS_ALLEGRO: " . $fdS[$PARAMETERS_ALLEGRO]->getStoreId() . " => " . $this->allegroCsId);
            $fdS[$PARAMETERS_ALLEGRO]->setStoreId($this->allegroCsId);
        }

        $s->save();

        return Command::SUCCESS;
    }
}
