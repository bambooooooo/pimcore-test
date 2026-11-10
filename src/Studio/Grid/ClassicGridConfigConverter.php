<?php

declare(strict_types=1);

namespace App\Studio\Grid;

/**
 * Converts the columns of a saved classic admin grid configuration (table gridconfigs) into Pimcore Studio
 * grid columns. Used by app:studio:migrate-grid-configs.
 *
 * Studio columns are taken from the "available columns" of the class, so type, group and config are exactly
 * what the Studio column picker would store. Classic columns without a Studio counterpart are reported.
 */
final class ClassicGridConfigConverter
{
    /**
     * @param array $classic decoded gridconfigs.config
     * @param array<string, array{type: string, group: array, localizable: bool, config: array}> $available
     *        Studio columns of the class by key (ColumnConfigurationService::getAvailableDataObjectColumnConfiguration)
     *
     * @return array{columns: list<array>, dropped: list<string>}
     */
    public function convert(array $classic, array $available): array
    {
        $language = $classic['language'] ?? null;
        $language = is_string($language) && $language !== '' && $language !== 'default' ? $language : 'pl';

        $columns = [];
        $dropped = [];
        $seen = [];

        $classicColumns = $classic['columns'] ?? [];
        uasort($classicColumns, static fn (array $a, array $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));

        foreach ($classicColumns as $name => $column)
        {
            $fieldConfig = $column['fieldConfig'] ?? [];
            $label = (string) ($fieldConfig['attributes']['label'] ?? $fieldConfig['label'] ?? $name);

            if (!empty($column['hidden']))
            {
                $dropped[] = "$label: hidden column";
                continue;
            }

            [$key, $note] = !empty($fieldConfig['isOperator'])
                ? $this->operatorKey($fieldConfig['attributes'] ?? [], $available)
                : [$this->fieldKey((string) ($fieldConfig['key'] ?? $name), $available), null];

            if ($key === null || !isset($available[$key]))
            {
                $dropped[] = "$label: " . ($note ?? 'no Studio column for ' . ($key ?? $name));
                continue;
            }

            if ($note !== null)
            {
                $dropped[] = "$label: $note";
            }

            $definition = $available[$key];
            $locale = $definition['localizable'] ? $language : null;
            $id = $key . '|' . $locale;

            if (isset($seen[$id]))
            {
                continue;
            }

            $seen[$id] = true;
            $columns[] = [
                'key' => $key,
                'locale' => $locale,
                'type' => $definition['type'],
                'group' => $definition['group'],
                'config' => $definition['config'],
                'width' => isset($column['width']) ? (int) $column['width'] : null,
            ];
        }

        return ['columns' => $columns, 'dropped' => $dropped];
    }

    /**
     * Plain field, system field or object brick field ("~Brick~Field").
     */
    private function fieldKey(string $key, array $available): ?string
    {
        if (isset($available[$key]))
        {
            return $key;
        }

        if (preg_match('/^~(\w+)~(\w+)$/', $key, $m))
        {
            foreach (array_keys($available) as $candidate)
            {
                if (str_ends_with($candidate, ".$m[1].$m[2]"))
                {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string} Studio key and a note about lost details
     */
    private function operatorKey(array $operator, array $available): array
    {
        $class = $operator['class'] ?? '';
        $children = $operator['children'] ?? [];

        if ($class === 'Alias' && count($children) === 1)
        {
            $child = $children[0];

            if (($child['type'] ?? '') === 'value')
            {
                return [$this->valueKey($child, $available), sprintf('header "%s" not kept (Studio uses the field title)', $operator['label'] ?? '')];
            }

            [$key, $note] = $this->operatorKey($child, $available);

            return [$key, $note ?? sprintf('header "%s" not kept (Studio uses the field title)', $operator['label'] ?? '')];
        }

        if ($class === 'PHPCode')
        {
            $phpClass = ltrim((string) ($operator['phpClass'] ?? ''), '\\');
            $data = (string) ($operator['additionalData'] ?? '');

            if ($phpClass === 'App\\Model\\Grid\\Price' && ctype_digit($data))
            {
                return [MegstylColumnResolver::offerPriceKey((int) $data), null];
            }

            if ($phpClass === 'App\\Model\\Grid\\PackageDim')
            {
                $params = json_decode($data, true);

                if (isset($params['n'], $params['d']))
                {
                    return [MegstylColumnResolver::packageDimensionKey((int) $params['n'], ucfirst((string) $params['d'])), null];
                }
            }

            return [null, "PHP operator $phpClass needs a Studio advanced column (see docs/techdocs/studio.md)"];
        }

        return [null, "operator $class needs a Studio advanced column (see docs/techdocs/studio.md)"];
    }

    private function valueKey(array $value, array $available): ?string
    {
        $attribute = $value['attribute'] ?? null;

        return is_string($attribute) && $attribute !== '' ? ($this->fieldKey($attribute, $available) ?? $attribute) : null;
    }
}
