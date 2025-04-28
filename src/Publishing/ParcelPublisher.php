<?php

namespace App\Publishing;

use Pimcore\Model\DataObject\Parcel;

class ParcelPublisher
{
    public function publish(Parcel $parcel): void
    {
        assert($parcel->getRules() && count($parcel->getRules()->getItems()) > 0, "Parcel rules can't be empty");
    }
}
