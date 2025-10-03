<?php

namespace App\Publishing;

use App\Message\PsMessage;
use Pimcore\Model\DataObject\Group;
use Symfony\Component\Messenger\MessageBusInterface;

class GroupPublisher
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {

    }
    public function publish(Group $group): void
    {
        if($group->isPublished())
        {
            $this->assertNamePL($group);
        }

        $this->messageBus->dispatch(new PsMessage($group->getId()));
    }
    private function assertNamePL(Group $group) : void
    {
        assert($group->getName("pl") and strlen($group->getName("pl")) > 0, "Product has to provide name in at least PL");
    }
}
