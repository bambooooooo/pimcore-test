<?php

namespace App\Publishing;

use Pimcore\Model\DataObject\Group;

class GroupPublisher
{
    public function publish(Group $group): void
    {
        $this->assertNamePL($group);
    }

    private function assertNamePL(Group $group) : void
    {
        assert($group->getName("pl") and strlen($group->getName("pl")) > 0, "Product has to provide name in at least PL");
    }
}
