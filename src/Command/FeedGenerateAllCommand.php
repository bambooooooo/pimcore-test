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

#[AsCommand(name: 'feed:generate:all', description: 'Sending feed generation message to all published feeds')]
class FeedGenerateAllCommand extends AbstractCommand
{
    public function __construct(private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $feeds = new Offer\Listing();
        $feeds->setUnpublished(false);
        $feeds->load();

        foreach ($feeds as $feed) {
            $this->bus->dispatch(new FeedMessage($feed->getId()));
        }

        $this->writeInfo(count($feeds) . ' feeds message have been generated.');

        return Command::SUCCESS;
    }
}
