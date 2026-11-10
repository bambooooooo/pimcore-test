<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\ElementInterface;

/**
 * Icon and tooltip of an element in the Pimcore Studio tree and editor tabs.
 *
 * Takes over the role of Pimcore\Model\Element\AdminStyle, which was removed together with the classic admin UI.
 * Subclasses set $elementIcon (a URL path) and override getElementQtipConfig() to build the tooltip.
 */
class AdminStyle
{
    protected ?string $elementIcon = null;

    private array $defaultQtipConfig;

    public function __construct(ElementInterface $element)
    {
        $this->defaultQtipConfig = [
            'title' => 'ID: ' . $element->getId(),
            'text' => 'Path: ' . htmlspecialchars($element->getRealFullPath()),
        ];
    }

    public function getElementIcon(): ?string
    {
        return $this->elementIcon;
    }

    /**
     * @return array{title: string, text: string}|null
     */
    public function getElementQtipConfig(): ?array
    {
        return $this->defaultQtipConfig;
    }

    public function getTooltip(): ?string
    {
        $qtip = $this->getElementQtipConfig();

        if (!$qtip)
        {
            return null;
        }

        return implode('<br/>', array_filter([$qtip['title'] ?? null, $qtip['text'] ?? null]));
    }
}
