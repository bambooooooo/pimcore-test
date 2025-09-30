<?php

namespace App\Command;

use App\Message\FeedMessage;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\Offer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'feed:generate', description: 'Generate feeds for given offer')]
class FeedGenerateCommand extends AbstractCommand
{
    public function __construct(private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('offer_id', InputArgument::REQUIRED, "Offer ID");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int)$input->getArgument('offer_id');

        if(!Offer::getById($id))
        {
            $this->writeError('Offer '.$id.' not found');
            return Command::FAILURE;
        }

        $this->bus->dispatch(new FeedMessage($id));
        return Command::SUCCESS;
    }
}
