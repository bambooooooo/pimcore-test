<?php

declare(strict_types=1);

namespace App\OptionProvider;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;

class FeedClassNameProvider implements SelectOptionsProviderInterface
{
    public function getOptions(array $context, Data $fieldDefinition): array
    {
        $options = [];

        foreach (glob(PIMCORE_PROJECT_ROOT . '/src/Feed/*.php') as $file) {
            $className = 'App\\Feed\\' . basename($file, '.php');

            if (class_exists($className)) {
                $options[] = [
                    'key'   => (new \ReflectionClass($className))->getShortName(),
                    'value' => $className,
                ];
            }
        }

        return $options;
    }

    public function hasStaticOptions(array $context, Data $fieldDefinition): bool
    {
        return true;
    }

    public function getDefaultValue(array $context, Data $fieldDefinition): string|array|null
    {
        return null;
    }
}
