<?php

namespace App\Publishing;

use Pimcore\Model\DataObject\Pricing;

class PricingPublisher
{
    public function publish(Pricing $pricing): void
    {
        assert($pricing->getRules() && count($pricing->getRules()->getItems()) > 0, "Pricing rules can't be empty");
        $this->assertNotRecursiveReference($pricing);
    }

    private function assertNotRecursiveReference(Pricing $pricing, array $alreadyFound = [])
    {
        foreach ($pricing->getRules() as $rule) {
            if($rule instanceof \Pimcore\Model\DataObject\Fieldcollection\Data\Pricing)
            {
                $nested = $rule->getPricing();

                if(in_array($nested, $alreadyFound))
                {
                    throw new \Exception("Recursive reference with " . $nested->getKey() . "(Id=" . $nested->getId() . ") detected");
                }

                $alreadyFound[] = $nested;

                $this->assertNotRecursiveReference($nested, $alreadyFound);
            }
        }

        return $alreadyFound;
    }
}
