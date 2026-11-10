<?php

declare(strict_types=1);

namespace App\Studio\Grid;

use App\Model\Grid\PackageDimension;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\ClassIdInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\ColumnCollectorInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\UseClassIdTrait;
use Pimcore\Bundle\StudioBackendBundle\Grid\Schema\ColumnConfiguration;
use Pimcore\Bundle\StudioBackendBundle\Util\Constant\ElementTypes;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Offer;

/**
 * Offers the Megstyl columns in the grid column picker of Product and ProductSet grids, group "Megstyl".
 */
final class MegstylColumnCollector implements ColumnCollectorInterface, ClassIdInterface
{
    use UseClassIdTrait;

    public const CLASSES = ['Product', 'ProductSet'];
    public const MAX_PACKAGES = 6;

    public function getCollectorName(): string
    {
        return 'megstyl';
    }

    public function supportedElementTypes(): array
    {
        return [ElementTypes::TYPE_DATA_OBJECT];
    }

    public function getColumnConfigurations(array $availableColumnDefinitions): array
    {
        $class = ClassDefinition::getById($this->getClassId());

        if (!$class || !in_array($class->getName(), self::CLASSES, true))
        {
            return [];
        }

        $columns = [];

        if ($definition = $availableColumnDefinitions[MegstylColumnDefinition::OFFER_PRICE] ?? null)
        {
            $offers = new Offer\Listing();
            $offers->setUnpublished(true);
            $offers->setOrderKey('key');

            foreach ($offers as $offer)
            {
                $columns[] = $this->column(MegstylColumnResolver::offerPriceKey($offer->getId()), ['Megstyl', 'Offer price', $offer->getKey() . ' (' . $offer->getId() . ')'], $definition);
            }
        }

        if ($definition = $availableColumnDefinitions[MegstylColumnDefinition::PACKAGE_DIMENSION] ?? null)
        {
            for ($n = 1; $n <= self::MAX_PACKAGES; $n++)
            {
                foreach (PackageDimension::DIMENSIONS as $dimension)
                {
                    $columns[] = $this->column(MegstylColumnResolver::packageDimensionKey($n, $dimension), ['Megstyl', 'Package dimension', "Package $n", $dimension], $definition);
                }
            }
        }

        return $columns;
    }

    private function column(string $key, array $group, MegstylColumnDefinition $definition): ColumnConfiguration
    {
        return new ColumnConfiguration(
            key: $key,
            group: $group,
            sortable: $definition->isSortable(),
            editable: false,
            exportable: $definition->isExportable(),
            filterable: $definition->isFilterable(),
            localizable: false,
            locale: null,
            type: $definition->getType(),
            frontendType: $definition->getFrontendType(),
            config: $definition->getConfig([]),
        );
    }
}
