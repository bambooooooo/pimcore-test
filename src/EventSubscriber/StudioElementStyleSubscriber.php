<?php

namespace App\EventSubscriber;

use App\Model\AdminStyle\AdminStyleResolver;
use Pimcore\Bundle\StudioBackendBundle\Asset\Event\PreResponse\AssetEvent;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Event\PreResponse\DataObjectDetailEvent;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Event\PreResponse\DataObjectEvent;
use Pimcore\Bundle\StudioBackendBundle\Element\Schema\CustomAttributes;
use Pimcore\Bundle\StudioBackendBundle\Response\ElementIcon;
use Pimcore\Bundle\StudioBackendBundle\Util\Constant\ElementIconTypes;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Applies App\Model\AdminStyle icons and tooltips to the Pimcore Studio tree and editor tabs.
 */
class StudioElementStyleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdminStyleResolver $resolver,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvent::EVENT_NAME => 'onDataObject',
            DataObjectDetailEvent::EVENT_NAME => 'onDataObjectDetail',
            AssetEvent::EVENT_NAME => 'onAsset',
        ];
    }

    public function onDataObject(DataObjectEvent $event): void
    {
        $this->apply($event->getCustomAttributes(), DataObject::getById($event->getDataObject()->getId()));
    }

    public function onDataObjectDetail(DataObjectDetailEvent $event): void
    {
        $this->apply($event->getCustomAttributes(), DataObject::getById($event->getDataObject()->getId()));
    }

    public function onAsset(AssetEvent $event): void
    {
        $this->apply($event->getCustomAttributes(), Asset::getById($event->getAsset()->getId()));
    }

    private function apply(CustomAttributes $attributes, ?ElementInterface $element): void
    {
        if (!$element || !$style = $this->resolver->resolve($element))
        {
            return;
        }

        try
        {
            if ($icon = $style->getElementIcon())
            {
                $attributes->setIcon(new ElementIcon(ElementIconTypes::PATH->value, $icon));
            }

            if ($tooltip = $style->getTooltip())
            {
                $attributes->setTooltip($tooltip);
            }
        }
        catch (\Throwable $e)
        {
            // A broken tooltip (e.g. missing relation) must never break the tree.
            $this->logger?->warning('Studio element style failed for {path}: {message}', [
                'path' => $element->getRealFullPath(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
