<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\DataObject\User;
use App\Service\DeepLService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Pimcore\Model\Notification\Service\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:instructions', description: 'Test command')]
class MoveInstructionCommand extends AbstractCommand
{
    public function __construct(private readonly NotificationService $notificationService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        DataObject::setGetInheritedValues(false);
        $products = new DataObject\Product\Listing();
        $products->setUnpublished(true);
        $products->setCondition("ObjectType != 'ACTUAL'");

        $cnt = 0;

        foreach ($products as $product) {

            if(!$product->getDocuments()) {
                continue;
            }

            $docs = [];
            $instruction = null;
            $instructionUS = null;

            foreach ($product->getDocuments() as $document) {

                if(str_contains($document->getKey(), "-IM-USA"))
                {
                    $instructionUS = $document;
                    continue;
                }
                if(str_contains($document->getKey(), "-IM"))
                {
                    $instruction = $document;
                    continue;
                }

                $docs[] = $document;
            }

            $changed = false;

            if($instruction)
            {
                $product->setInstruction($instruction);
                $changed = true;
            }

            if($instructionUS)
            {
                $product->setInstructionUS($instructionUS);
                $changed = true;
            }

            if($changed)
            {
                $product->setDocuments($docs);
                $product->save();

                $this->writeInfo($product->getKey() . " updated.");
            }
            else
            {
                $this->writeComment($product->getKey() . " skipped.");
            }
        }

        return Command::SUCCESS;
    }
}
