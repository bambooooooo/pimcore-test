<?php

/**
 * Inheritance: no
 * Variants: no
 * Oferta
 * 
 * Ofertę stanowi lista Wycen w ustalonej kolejności. Cena produktu w ofercie to pierwsza z Wycen, dla której zostaną spełnione ograniczenia.
 *
 * Fields Summary:
 * - Pricings [manyToManyObjectRelation]
 * - Baselinker [manyToOneRelation]
 * - BaselinkerCatalog [manyToOneRelation]
 * - BaselinkerPriceGroupId [numeric]
 * - Currency [select]
 * - Image [image]
 * - localizedfields [localizedfields]
 * -- Name [input]
 * -- Summary [wysiwyg]
 * - Description [fieldcollections]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'Offer',
   'name' => 'Offer',
   'title' => '',
   'description' => 'Oferta

Ofertę stanowi lista Wycen w ustalonej kolejności. Cena produktu w ofercie to pierwsza z Wycen, dla której zostaną spełnione ograniczenia.',
   'creationDate' => NULL,
   'modificationDate' => 1753431154,
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
   'allowInherit' => false,
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
             'name' => 'System',
             'type' => NULL,
             'region' => NULL,
             'title' => 'System',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
                 'name' => 'Pricings',
                 'title' => 'Pricings',
                 'tooltip' => 'Lista Wycen w odpowiedniej kolejności

Przykładowo:
1. Cena bazowa: 0 - 300 --> Cena bazowa * 1.5
2. Cena bazowa: 300 - 500 --> Cena bazowa * 1.4
3. Cena bazowa: 500 - 800 --> Cena bazowa * 1.35
4. Cena bazowa: 800 - 1 100 --> Cena bazowa * 1.32
5. (brak ograniczeń) --> Cena bazowa * 1.3',
                 'mandatory' => true,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => true,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'classes' => 
                array (
                  0 => 
                  array (
                    'classes' => 'Pricing',
                  ),
                ),
                 'displayMode' => 'grid',
                 'pathFormatterClass' => '',
                 'maxItems' => NULL,
                 'visibleFields' => 'fullpath',
                 'allowToCreateNewObject' => false,
                 'allowToClearRelation' => false,
                 'optimizedAdminLoading' => false,
                 'enableTextSelection' => false,
                 'visibleFieldDefinitions' => 
                array (
                ),
                 'width' => '',
                 'height' => '',
              )),
              1 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                 'name' => 'Baselinker',
                 'title' => 'Baselinker',
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => true,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'classes' => 
                array (
                  0 => 
                  array (
                    'classes' => 'Baselinker',
                  ),
                ),
                 'displayMode' => 'grid',
                 'pathFormatterClass' => '',
                 'assetInlineDownloadAllowed' => false,
                 'assetUploadPath' => '',
                 'allowToClearRelation' => true,
                 'objectsAllowed' => true,
                 'assetsAllowed' => false,
                 'assetTypes' => 
                array (
                ),
                 'documentsAllowed' => false,
                 'documentTypes' => 
                array (
                ),
                 'width' => '',
              )),
              2 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                 'name' => 'BaselinkerCatalog',
                 'title' => 'Baselinker Catalog',
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => '',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => true,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'classes' => 
                array (
                  0 => 
                  array (
                    'classes' => 'BaselinkerCatalog',
                  ),
                ),
                 'displayMode' => 'grid',
                 'pathFormatterClass' => '',
                 'assetInlineDownloadAllowed' => false,
                 'assetUploadPath' => '',
                 'allowToClearRelation' => true,
                 'objectsAllowed' => true,
                 'assetsAllowed' => false,
                 'assetTypes' => 
                array (
                ),
                 'documentsAllowed' => false,
                 'documentTypes' => 
                array (
                ),
                 'width' => '',
              )),
              3 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
                 'name' => 'BaselinkerPriceGroupId',
                 'title' => 'Baselinker Price Group Id',
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => true,
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
                 'defaultValue' => NULL,
                 'integer' => false,
                 'unsigned' => false,
                 'minValue' => NULL,
                 'maxValue' => NULL,
                 'unique' => false,
                 'decimalSize' => NULL,
                 'decimalPrecision' => NULL,
                 'width' => '',
                 'defaultValueGenerator' => '',
              )),
              4 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
                 'name' => 'Currency',
                 'title' => 'Currency',
                 'tooltip' => 'Waluta dla oferty',
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
                 'options' => 
                array (
                  0 => 
                  array (
                    'key' => 'PLN',
                    'value' => 'PLN',
                  ),
                  1 => 
                  array (
                    'key' => 'EUR',
                    'value' => 'EUR',
                  ),
                  2 => 
                  array (
                    'key' => 'USD',
                    'value' => 'USD',
                  ),
                  3 => 
                  array (
                    'key' => 'GBP',
                    'value' => 'GBP',
                  ),
                ),
                 'defaultValue' => 'PLN',
                 'columnLength' => 190,
                 'dynamicOptions' => false,
                 'defaultValueGenerator' => '',
                 'width' => '',
                 'optionsProviderType' => 'configure',
                 'optionsProviderClass' => '',
                 'optionsProviderData' => '',
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
             'name' => 'Cover',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Cover',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Image::__set_state(array(
                 'name' => 'Image',
                 'title' => 'Image',
                 'tooltip' => 'Zdjęcie główne grupy',
                 'mandatory' => true,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => 'float: right; margin: 8px;',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'uploadPath' => '/KATEGORIA-ZDJĘCIA',
                 'width' => 506,
                 'height' => 357,
              )),
              1 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
                 'name' => 'localizedfields',
                 'title' => '',
                 'tooltip' => NULL,
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => NULL,
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => true,
                 'visibleSearch' => true,
                 'blockedVarsForExport' => 
                array (
                ),
                 'children' => 
                array (
                  0 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                     'name' => 'Name',
                     'title' => 'Name',
                     'tooltip' => 'Nazwa grupy',
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
                     'defaultValue' => NULL,
                     'columnLength' => 100,
                     'regex' => '',
                     'regexFlags' => 
                    array (
                    ),
                     'unique' => false,
                     'showCharCount' => true,
                     'width' => 500,
                     'defaultValueGenerator' => '',
                  )),
                  1 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                     'name' => 'Summary',
                     'title' => 'Summary',
                     'tooltip' => 'Podsumowanie',
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
                     'toolbarConfig' => '',
                     'excludeFromSearchIndex' => false,
                     'maxCharacters' => '',
                     'height' => '',
                     'width' => '100%',
                  )),
                ),
                 'region' => NULL,
                 'layout' => NULL,
                 'maxTabs' => NULL,
                 'border' => false,
                 'provideSplitView' => false,
                 'tabPosition' => 'top',
                 'hideLabelsWhenTabsReached' => NULL,
                 'referencedFields' => 
                array (
                ),
                 'permissionView' => NULL,
                 'permissionEdit' => NULL,
                 'labelWidth' => 100,
                 'labelAlign' => 'left',
                 'width' => '',
                 'height' => '',
                 'fieldDefinitionsCache' => NULL,
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' => 
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => NULL,
             'border' => false,
             'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggZmlsbD0ibm9uZSIgZD0iTTAsMGgyNHYyNEgwVjB6Ii8+DQo8cGF0aCBmaWxsPSIjRjA3ODI4IiBkPSJNMTYsNEg0QzIuOSw0LDIsNC45LDIsNnYxMmMwLDEuMSwwLjksMiwyLDJoMTZjMS4xLDAsMi0wLjksMi0ydi04TDE2LDR6Ii8+DQo8cG9seWdvbiBmaWxsPSIjRjdCNjgxIiBwb2ludHM9IjE2LDQgMTYsMTAgMjIsMTAgIi8+DQo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMTEsMTUuNWwtMi41LTNMNSwxN2gxNGwtNC41LTZMMTEsMTUuNXoiLz4NCjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjYuNSIgY3k9IjguNSIgcj0iMS41Ii8+DQo8Y2lyY2xlIGZpbGw9IiM4NTg3ODkiIGN4PSIxOCIgY3k9IjE4IiByPSI2Ii8+DQo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMjEuOSwxOUwyMSwxOC40YzAtMC4xLDAtMC4zLDAtMC40czAtMC4zLDAtMC40bDAuOC0wLjZjMC4xLTAuMSwwLTAuMSwwLTAuMkwyMSwxNS4zYzAtMC4xLTAuMS0wLjEtMC4yLTAuMQ0KCWMwLDAsMCwwLTAuMSwwbC0wLjksMC40Yy0wLjEsMC0wLjEtMC4xLTAuMi0wLjFjLTAuMi0wLjItMC40LTAuMi0wLjUtMC4zbC0wLjItMS4xYzAsMCwwLTAuMS0wLjItMC4xaC0xLjVjLTAuMSwwLTAuMiwwLjEtMC4yLDAuMg0KCWwtMC4yLDEuMWMtMC4yLDAuMS0wLjQsMC4yLTAuNiwwLjNsLTEuMS0wLjRjLTAuMSwwLTAuMiwwLTAuMiwwLjFsLTAuOCwxLjRjMCwwLjEsMCwwLjEsMC4xLDAuMmwwLjgsMC43YzAsMC4yLDAsMC4zLDAsMC40DQoJczAsMC4yLDAsMC40TDE0LjIsMTljMCwwLTAuMSwwLjEsMCwwLjJsMC44LDEuNGMwLDAuMSwwLjEsMC4xLDAuMiwwLjFjMCwwLDAsMCwwLjEsMGwwLjktMC4zYzAuMiwwLjIsMC41LDAuMywwLjgsMC40bDAuMiwwLjkNCgljMCwwLjIsMC4xLDAuMywwLjIsMC4zaDEuNGMwLjEsMCwwLjItMC4xLDAuMi0wLjJsMC4yLTFjMC4yLTAuMSwwLjUtMC4yLDAuNy0wLjRsMSwwLjRjMCwwLDAsMCwwLjEsMHMwLjEsMCwwLjItMC4xbDAuOC0xLjQNCglDMjIsMTkuMywyMi4xLDE5LjEsMjEuOSwxOXogTTE4LDE5LjVjLTAuOSwwLTEuNS0wLjctMS41LTEuNXMwLjYtMS41LDEuNS0xLjVjMC44LDAsMS41LDAuNywxLjUsMS41UzE4LjgsMTkuNSwxOCwxOS41eiIvPg0KPC9zdmc+DQo=',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Description',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Description',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
                 'name' => 'Description',
                 'title' => 'Description',
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
                  0 => 'Image',
                  1 => 'ImageText',
                  2 => 'ImageWideo',
                  3 => 'TextImage',
                  4 => 'Text',
                  5 => 'TextWideo',
                  6 => 'WideoImage',
                  7 => 'Wideo',
                  8 => 'WideoText',
                ),
                 'lazyLoading' => true,
                 'maxItems' => NULL,
                 'disallowAddRemove' => false,
                 'disallowReorder' => false,
                 'collapsed' => false,
                 'collapsible' => false,
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
             'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIyLjEuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8cGF0aCBmaWxsPSIjNUI5Q0Y1IiBkPSJNMjEsNWMtMS4xLTAuMy0yLjMtMC41LTMuNS0wLjVjLTEuOSwwLTQuMSwwLjQtNS41LDEuNWMtMS40LTEuMS0zLjYtMS41LTUuNS0xLjVTMi41LDQuOSwxLDZ2MTQuNgoJYzAsMC4zLDAuMywwLjUsMC41LDAuNWMwLjEsMCwwLjEsMCwwLjMsMEMzLjEsMjAuNSw1LjEsMjAsNi41LDIwYzEuOSwwLDQuMSwwLjQsNS41LDEuNWMxLjQtMC45LDMuOC0xLjUsNS41LTEuNQoJYzEuNiwwLDMuNCwwLjMsNC44LDFjMC4xLDAuMSwwLjEsMC4xLDAuMywwLjFjMC4zLDAsMC41LTAuMywwLjUtMC41VjZDMjIuNCw1LjYsMjEuOCw1LjMsMjEsNSIvPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMjEsMTguNWMtMS4xLTAuNC0yLjMtMC41LTMuNS0wLjVjLTEuNywwLTQuMSwwLjYtNS41LDEuNVY4YzEuNC0wLjgsMy44LTEuNSw1LjUtMS41YzEuMiwwLDIuNCwwLjIsMy41LDAuNQoJVjE4LjV6Ii8+Cjwvc3ZnPgo=',
             'labelWidth' => 100,
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
   'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/percent.svg',
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
