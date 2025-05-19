<?php

/**
 * Inheritance: yes
 * Variants: no
 * Title: Pricing
 * Wycena
 *
 * Fields Summary:
 * - Countries [countrymultiselect]
 * - Restrictions [objectbricks]
 * - UseBasePrice [checkbox]
 * - Rules [fieldcollections]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'Parcel',
   'name' => 'Pricing',
   'title' => 'Pricing',
   'description' => 'Wycena',
   'creationDate' => NULL,
   'modificationDate' => 1747630272,
   'userOwner' => 2,
   'userModification' => 2,
   'parentClass' => '',
   'implementsInterfaces' => '',
   'listingParentClass' => '',
   'useTraits' => '',
   'listingUseTraits' => '',
   'encryption' => false,
   'encryptedTables' =>
  array (
  ),
   'allowInherit' => true,
   'allowVariants' => false,
   'showVariants' => false,
   'layoutDefinitions' =>
  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'name' => 'pimcore_root',
     'type' => NULL,
     'region' => NULL,
     'title' => NULL,
     'width' => 0,
     'height' => 0,
     'collapsible' => false,
     'collapsed' => false,
     'bodyStyle' => NULL,
     'datatype' => 'layout',
     'children' =>
    array (
      0 =>
      \Pimcore\Model\DataObject\ClassDefinition\Layout\Tabpanel::__set_state(array(
         'name' => 'Layout',
         'type' => NULL,
         'region' => NULL,
         'title' => '',
         'width' => '',
         'height' => '',
         'collapsible' => false,
         'collapsed' => false,
         'bodyStyle' => '',
         'datatype' => 'layout',
         'children' =>
        array (
          0 =>
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'System data',
             'type' => NULL,
             'region' => NULL,
             'title' => 'System data',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' =>
            array (
              0 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Countrymultiselect::__set_state(array(
                 'name' => 'Countries',
                 'title' => 'Countries',
                 'tooltip' => 'Kraje

Kraj, w którym obowiązuje usługa
',
                 'mandatory' => true,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'maxItems' => NULL,
                 'renderType' => 'tags',
                 'dynamicOptions' => false,
                 'defaultValue' => NULL,
                 'height' => '',
                 'width' => '',
                 'defaultValueGenerator' => '',
                 'optionsProviderType' => NULL,
                 'optionsProviderClass' => NULL,
                 'optionsProviderData' => NULL,
                 'restrictTo' => '',
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' =>
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => NULL,
             'border' => false,
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          1 =>
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Restrictions',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Restrictions',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' =>
            array (
              0 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks::__set_state(array(
                 'name' => 'Restrictions',
                 'title' => 'Restrictions',
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'allowedTypes' =>
                array (
                  0 => 'MaxPackageLength',
                  1 => 'MaxPackageSideLengthSum',
                  2 => 'MaxPackageWeight',
                  3 => 'LoadCarriers',
                  4 => 'MinimalProfit',
                  5 => 'SelectedGroups',
                  6 => 'BasePrice',
                  7 => 'ProductDimensions',
                  8 => 'ProductCOO',
                  9 => 'MinimalPercentageProfit',
                  10 => 'MinimalMarkup',
                ),
                 'maxItems' => NULL,
                 'border' => false,
              )),
              1 =>
              \Pimcore\Model\DataObject\ClassDefinition\Layout\Text::__set_state(array(
                 'name' => 'Layout',
                 'type' => NULL,
                 'region' => NULL,
                 'title' => '',
                 'width' => '',
                 'height' => '',
                 'collapsible' => false,
                 'collapsed' => false,
                 'bodyStyle' => '',
                 'datatype' => 'layout',
                 'children' =>
                array (
                ),
                 'locked' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'fieldtype' => 'text',
                 'html' => '<h1>Ograniczenia</h1>

Wymagania, które musi spełnić produkt, aby wycena była możliwa',
                 'renderingClass' => '',
                 'renderingData' => '',
                 'border' => false,
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' =>
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => NULL,
             'border' => false,
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/ruler.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          2 =>
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Pricing',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Pricing',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' =>
            array (
              0 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
                 'name' => 'UseBasePrice',
                 'title' => 'Use Base Price',
                 'tooltip' => 'Czy użyć w ceny bazowej jako podstawa obliczeń? W przeciwnym wypadku użyta zostanie wartość 0,00 zł',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'defaultValue' => 1,
                 'defaultValueGenerator' => '',
              )),
              1 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
                 'name' => 'Rules',
                 'title' => 'Rules',
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'allowedTypes' =>
                array (
                  0 => 'Factor',
                  1 => 'Surcharge',
                  2 => 'ParcelMassVolume',
                  3 => 'Pricing',
                ),
                 'lazyLoading' => true,
                 'maxItems' => NULL,
                 'disallowAddRemove' => false,
                 'disallowReorder' => false,
                 'collapsed' => false,
                 'collapsible' => false,
                 'border' => false,
              )),
              2 =>
              \Pimcore\Model\DataObject\ClassDefinition\Layout\Text::__set_state(array(
                 'name' => 'Layout',
                 'type' => NULL,
                 'region' => NULL,
                 'title' => '',
                 'width' => '',
                 'height' => '',
                 'collapsible' => false,
                 'collapsed' => false,
                 'bodyStyle' => '',
                 'datatype' => 'layout',
                 'children' =>
                array (
                ),
                 'locked' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'fieldtype' => 'text',
                 'html' => '<h1>Reguły</h1>

Kroki służące do wyznaczenia końcowej ceny usługi',
                 'renderingClass' => '',
                 'renderingData' => '',
                 'border' => false,
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' =>
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => NULL,
             'border' => false,
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/sales_performance.svg',
             'labelWidth' => 180,
             'labelAlign' => 'left',
          )),
        ),
         'locked' => false,
         'blockedVarsForExport' =>
        array (
        ),
         'fieldtype' => 'tabpanel',
         'border' => false,
         'tabPosition' => 'top',
      )),
    ),
     'locked' => false,
     'blockedVarsForExport' =>
    array (
    ),
     'fieldtype' => 'panel',
     'layout' => NULL,
     'border' => false,
     'icon' => NULL,
     'labelWidth' => 100,
     'labelAlign' => 'left',
  )),
   'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGNpcmNsZSBmaWxsPSIjMjhBMDUwIiBjeD0iOCIgY3k9IjgiIHI9IjciLz4NCjxjaXJjbGUgZmlsbD0iIzI4NzhGMCIgY3g9IjE2IiBjeT0iMTYiIHI9IjciLz4NCjxnPg0KCTxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik05LDkuN2MwLTAuMy0wLjEtMC41LTAuMi0wLjZDOC42LDguOSw4LjQsOC44LDgsOC42UzcuNCw4LjQsNy4xLDguM1M2LjYsOCw2LjQsNy44QzYuMyw3LjYsNi4xLDcuNCw2LDcuMg0KCQlDNS45LDcsNS45LDYuNyw1LjksNi40YzAtMC42LDAuMi0xLDAuNS0xLjRzMC44LTAuNiwxLjQtMC42di0xaDAuOHYxLjFjMC42LDAuMSwxLDAuMywxLjQsMC43czAuNSwwLjksMC41LDEuNkg5DQoJCWMwLTAuNS0wLjEtMC44LTAuMi0xQzguNiw1LjYsOC40LDUuNSw4LjEsNS41Yy0wLjMsMC0wLjUsMC4xLTAuNiwwLjJTNy4zLDYuMSw3LjMsNi40YzAsMC4yLDAuMSwwLjQsMC4yLDAuNnMwLjQsMC4zLDAuOCwwLjUNCgkJYzAuNCwwLjIsMC43LDAuMywxLDAuNHMwLjUsMC4zLDAuNiwwLjVzMC4zLDAuNCwwLjQsMC42czAuMSwwLjUsMC4xLDAuOGMwLDAuNi0wLjIsMS0wLjUsMS4zcy0wLjgsMC41LTEuNCwwLjZ2MUg3Ljd2LTENCgkJQzcsMTEuNiw2LjUsMTEuNCw2LjEsMTFjLTAuMy0wLjQtMC41LTEtMC41LTEuNkg3YzAsMC40LDAuMSwwLjcsMC4zLDAuOWMwLjIsMC4yLDAuNCwwLjMsMC44LDAuM2MwLjMsMCwwLjUtMC4xLDAuNy0wLjINCgkJUzksMTAsOSw5Ljd6Ii8+DQo8L2c+DQo8Zz4NCgk8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMTYuOSwxN2gtMS42YzAsMC41LDAuMSwwLjgsMC40LDEuMWMwLjIsMC4yLDAuNiwwLjQsMS4xLDAuNGMwLjMsMCwwLjctMC4xLDAuOS0wLjJsMC4yLDEuMg0KCQljLTAuNCwwLjEtMC44LDAuMS0xLjMsMC4xYy0wLjksMC0xLjUtMC4yLTItMC43cy0wLjgtMS4xLTAuOC0xLjlIMTN2LTAuN2gwLjh2LTAuNkgxM1YxNWgwLjhjMC4xLTAuOCwwLjMtMS41LDAuOS0xLjkNCgkJYzAuNS0wLjUsMS4yLTAuNywyLjEtMC43YzAuMywwLDAuNywwLjEsMS4yLDAuMmwtMC4yLDEuMmMtMC4zLTAuMS0wLjYtMC4yLTAuOS0wLjJjLTAuOSwwLTEuNCwwLjUtMS41LDEuNEgxN3YwLjdoLTEuNnYwLjZIMTdWMTcNCgkJSDE2Ljl6Ii8+DQo8L2c+DQo8L3N2Zz4NCg==',
   'group' => '',
   'showAppLoggerTab' => false,
   'linkGeneratorReference' => '',
   'previewGeneratorReference' => '',
   'compositeIndices' =>
  array (
  ),
   'showFieldLookup' => false,
   'propertyVisibility' =>
  array (
    'grid' =>
    array (
      'id' => true,
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
    'search' =>
    array (
      'id' => true,
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
  ),
   'enableGridLocking' => false,
   'deletedDataComponents' =>
  array (
  ),
   'blockedVarsForExport' =>
  array (
  ),
   'fieldDefinitionsCache' =>
  array (
  ),
   'activeDispatchingEvents' =>
  array (
  ),
));
